@component('mail::message')
# Advance Salary Request Received

Dear {{ $salary->employee->name }},

This email is to confirm that your request for an advance salary has been successfully submitted and is currently being reviewed by the administration.

**Request Details:**
@component('mail::table')
| Detail          | Information                          |
| :-------------- | :----------------------------------- |
| **Amount**      | {{ currency_format($salary->advance_salary, $salary->employee->company->currency_id) }} |
| **Date Requested**| {{ $salary->date }}                |
| **Status**      | {{ ucfirst($salary->status) }}       |
@endcomponent

You will receive another notification once your request has been approved or updated.

@component('mail::button', ['url' => route('advance-salaries.index')])
View Request Status
@endcomponent

Thanks,<br>
{{ config('app.name') }} HR Team
@endcomponent