<?php

namespace App\Exports\Tenants\Admin;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use App\Models\Exports;
use App\Models\Ticket;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Events\AfterSheet;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;

class TicketsExport implements FromQuery, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'tickets.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $export_record_id;
    protected $status;
    protected $category;
    protected $date_from;
    protected $date_to;
    protected $search;

    public function __construct($export_record_id, $status, $category, $date_from, $date_to, $search)
    {
        $this->export_record_id;
        $this->status = $status;
        $this->category = $category;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->search = $search;
    }

    public function afterWriting(AfterSheet $event) 
    {

        $exportRecord = Exports::find($this->export_record_id);
        
        $exportRecord->update([
            'status' => 'completed',
        ]);

        $exportRecord->save();
    }

    
    public function chunkSize(): int
    {
        return 1000; // adjust as needed
    }

    public function query()
    {
        
        $searchParts = explode(' ', $this->search);

        return Ticket::query()->with('user')
            ->when($this->category, function ($query) {
                $query->where('concern_category', $this->category);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when(($this->date_from || $this->date_to), function ($query) {
                FilterHelper::queryCreatedAt($query, $this->date_from, $this->date_to);
            })
            ->when(!empty($this->search), function ($query) use ( $searchParts) {
                $query->whereHas('user', function ($query) use ($searchParts) {
                    Helper::queryNameSearch($searchParts, $query);
                });
            })
            ->orderBy('created_at', 'DESC')
            ->whereHas('user');
    }

    public function map($data): array
    {
        $status = ['Open', 'Pending', 'On Hold', 'Solved', 'Closed'];

        return [
            Carbon::parse($data->created_at)->toDateString(),
            Carbon::parse($data->created_at)->toTimeString(),
            $data->user->first_name.' '. $data->user->last_name . ' '. $data->user->suffix,
            $data->concern_category,
            $data->content,
            $status[$data->status - 1],
            $data->updated_at,
            $data->status == 4 ? Carbon::parse($data->updated_at)->toDateString() : '',
            $data->status == 4 ? Carbon::parse($data->updated_at)->toTimeString() : ''
        ];
    }

    public function headings(): array
    {
        
        return [
            'Date Received',
            'Time Received',
            'Name', 
            'Category',
            'Concern',
            'Status',
            'Status Updated At',
            'Date Resolved',
            'Time Resolved'
        ];
    }
}
