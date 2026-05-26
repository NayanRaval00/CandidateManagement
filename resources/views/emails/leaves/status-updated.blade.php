<x-mail::message>
# Leave Request {{ ucfirst($leaveRequest->status) }}

Hello **{{ $leaveRequest->user->name }}**,

Your leave request for **{{ $leaveRequest->leaveType->name }}** from **{{ $leaveRequest->start_date->format('Y-m-d') }}** to **{{ $leaveRequest->end_date->format('Y-m-d') }}** ({{ $leaveRequest->days }} days) has been **{{ $leaveRequest->status }}**.

@if($leaveRequest->rejection_reason)
**Manager's Notes / Rejection Reason:**
{{ $leaveRequest->rejection_reason }}
@endif

<x-mail::button :url="url('/admin/leave-requests')">
View Leave History
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
