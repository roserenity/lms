<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Course;
use App\Models\User;

class TenantAssessmentSeeder extends Seeder
{
  /**
  * Run the database seeds.
  *
  * @return void
  */
  public function run()
  {
    $user = User::where('role', USER::ROLE_INSTRUCTOR)->first();

    $course = Course::find(1);

    $module = $course->modules()->orderBy('id')->first();

    $assessment = $course->assessments()->create([
      'module_id' => $module->id,
      'uploader_id' => $user->id,
      'title' => 'Sample Quiz',
      'type_of_assessment' => 'graded_assessment',
      'description' => 'A short quiz to try out the assessment flow.',
      'max_attempt' => 3,
      'accepted_score' => 'highest',
      'passing_score' => 50,
      'view_results_by' => 'all_correct_answers'
    ]);

    // Choices and correct answers use the same keys the question builder saves (choice_0, choice_1, ...).
    $questions = [
      [
        'question' => 'Which role creates courses and assessments?',
        'type_of_question' => 'multiple_choice',
        'choices' => ['Learner', 'Instructor', 'Guest'],
        'correct_answer' => 'choice_1'
      ],
      [
        'question' => 'A learner receives a certificate after completing a course.',
        'type_of_question' => 'true_or_false',
        'choices' => ['TRUE', 'FALSE'],
        'correct_answer' => 'choice_0'
      ],
      [
        'question' => 'Which of these can be added to a course module?',
        'type_of_question' => 'multiple_choice',
        'choices' => ['A topic', 'A printer', 'A spreadsheet formula'],
        'correct_answer' => 'choice_0'
      ]
    ];

    foreach ($questions as $key => $item) {
      $question = $assessment->assessment_questions()->create([
        'question' => $item['question'],
        'type_of_question' => $item['type_of_question'],
        'score' => 1,
        'sort_no' => $key + 1
      ]);

      $choices = [];
      foreach ($item['choices'] as $index => $choice) {
        $choices[] = ["choice_{$index}" => $choice, 'feedback' => ''];
      }

      $question->assessment_answer()->create([
        'correct_answer' => $item['correct_answer'],
        'choices' => $choices
      ]);
    }

    $assessment->update(['total_points' => count($questions)]);

    $module->module_content()->create([
      'module_id' => $module->id,
      'contentable_id' => $assessment->id,
      'contentable_type' => 'App\\Models\\Assessment',
      'sort_no' => $module->module_content()->count() + 1
    ]);
  }
}
