<x-mail::message>
# New Leave Request Submitted

Hello,

A new leave request has been submitted by **{{ $leaveRequest->user->name }}**.

**Details:**
- **Leave Type:** {{ $leaveRequest->leaveType->name }}
- **Start Date:** {{ $leaveRequest->start_date->format('Y-m-d') }}
- **End Date:** {{ $leaveRequest->end_date->format('Y-m-d') }}
- **Total Days:** {{ $leaveRequest->days }} days
- **Reason:** {{ $leaveRequest->reason }}

Please log in to the admin panel to approve or reject this request.

<x-mail::button :url="url('/admin/leave-requests')">
View Leave Requests
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
