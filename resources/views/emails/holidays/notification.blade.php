@component('mail::message')
# Holiday Announcement: {{ $holiday->name }}

Hello,

This is to inform you that **{{ $isToday ? 'today' : 'tomorrow' }} ({{ $holiday->date->format('F d, Y') }})** is a holiday for **{{ $holiday->name }}**.

@if($holiday->is_working_day)
@component('mail::panel')
**Notice:** This is a **working holiday**. The office will remain open, and normal working hours apply.
@endcomponent
@else
@component('mail::panel')
**Notice:** The **office will be closed**, and employees are not expected to report to work.
@endcomponent
@endif

@if($holiday->description)
### Description:
{{ $holiday->description }}
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
