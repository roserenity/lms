<?php

namespace App\Exports\Tenants\Admin;


use App\Models\User;
use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;
use App\Helpers\Helper;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromQuery;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Helpers\FilterHelper;

class UserManagementExport implements FromQuery, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'users.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $role;
    protected $status;
    protected $search;
    protected $created_to;
    protected $created_from;
    protected $other_filters;
    protected $request;

    public function __construct($role, $status, $search, $created_to, $created_from, $other_filters, $request)
    {
        $this->role = $role;
        $this->status = $status;
        $this->search = $search;
        $this->created_to = $created_to;
        $this->created_from = $created_from;
        $this->other_filters = $other_filters;
        $this->request = $request;
    }

    public function query()
    {
        $searchParts = explode(' ', $this->search);

        $query = User::query();

        // Custom registration field filtering
        if (count($this->other_filters) > 0) {
            $pages = CustomRegistrationPage::select(['title', 'id'])
                ->with(['custom_registration_fields' => function ($innerquery) {
                    $innerquery->whereIn('input_field_name', $this->other_filters)
                            ->select(['input_field_name', 'id', 'custom_registration_page_id']);
                }])
                ->get();

            foreach ($pages as $page) {
                foreach ($page->custom_registration_fields as $field) {
                    $query->when($this->request[$field->input_field_name] ?? null, function ($subquery) use ($page, $field) {
                        $nestedKey = 'data->' . $page->title . '->' . $field->input_field_name;
                        $filterValue = $this->request[$field->input_field_name];

                        if (is_array($filterValue)) {
                            foreach ($filterValue as $value) {
                                $subquery->orWhereJsonContains($nestedKey, $value);
                            }
                        } else {
                            $subquery->orWhereJsonContains($nestedKey, $filterValue);
                        }
                    });
                }
            }
        }

        // Name or email search
        if ($this->search) {
            Helper::queryNameEmailSearch($searchParts, $query);
        }

        // Role filter
        if ($this->role) {
            is_array($this->role)
                ? $query->whereIn('role', $this->role)
                : $query->where('role', $this->role);
        }

        // Status filter
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // Date range filter
        if ($this->created_from || $this->created_to) {
            FilterHelper::queryCreatedAt($query, $this->created_from, $this->created_to);
        }

        return $query->orderBy('created_at', 'desc')->select('*');
    }


    public function map($data): array
    {
        // Decode JSON column if needed
        $data->data = is_string($data->data) ? json_decode($data->data, true) : ($data->data ?? []);

        $custom_registration_pages = CustomRegistrationPage::all();
        $user_data = [];

        foreach ($custom_registration_pages as $page) {
            $fields = CustomRegistrationField::where('custom_registration_page_id', $page->id)->get();
            foreach ($fields as $field) {
                $value = $data->data[$page->title][$field->input_field_name] ?? '';

                if ($field->input_field_type == 'dropdown' && $field->is_multiple) {
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                    } else {
                        $decoded = $value;
                    }
                
                    $user_data[] = is_array($decoded) ? implode(', ', $decoded) : (string) $decoded;
                } else {
                    $user_data[] = is_array($value) ? implode(', ', $value) : $value;
                }
                
            }
        }

        return [
            $data->role,
            $data->role === 'USER' ? $data->enrollee_id : '',
            $data->first_name,
            $data->middle_name,
            $data->last_name,
            $data->suffix,
            $data->status,
            $data->email,
            ...$user_data
        ];
    }


    public function headings(): array
    {
        $custom_registration_pages = CustomRegistrationPage::all();
        $custom_fields = [];

        foreach ($custom_registration_pages as $page) {
            $labels = CustomRegistrationField::where('custom_registration_page_id', $page->id)
                        ->pluck('input_field_label')
                        ->toArray();

            $custom_fields = array_merge($custom_fields, $labels);
        }

        return [
            'Role',
            'Enrollee ID',
            'First Name',
            'Middle Name',
            'Last Name', 
            'Suffix',
            'Status',
            'Email',
            ...$custom_fields
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }


}
