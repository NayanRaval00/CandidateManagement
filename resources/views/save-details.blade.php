<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Application Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 500;
        }
        .btn-primary {
            background-color: #0056d2;
            border-color: #0056d2;
        }
        .form-section-title {
            font-size: 1.1rem;
            margin-top: 2rem;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: .5rem;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <div class="card p-4">
                <h2 class="text-center mb-4">Innoventix Job Application Form</h2>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mobile Number *</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Position Applying For *</label>
                            <input type="text" name="position" class="form-control" value="{{ old('position') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">State *</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state') }}" required>
                        </div>
                    </div>

                    <div class="form-section-title">Professional Details</div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Current Company Name</label>
                            <input type="text" name="current_company_name" class="form-control" value="{{ old('current_company_name') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Current Position</label>
                            <input type="text" name="current_position" class="form-control" value="{{ old('current_position') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Current CTC (in ₹)</label>
                            <input type="number" name="current_ctc" step="0.01" class="form-control" value="{{ old('current_ctc') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Expected CTC (in ₹)</label>
                            <input type="number" name="expected_ctc" step="0.01" class="form-control" value="{{ old('expected_ctc') }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Notice Period</label>
                            <input type="text" name="notice_period" class="form-control" value="{{ old('notice_period') }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Reason for Job Change</label>
                            <textarea name="reason_for_job_change" rows="3" class="form-control">{{ old('reason_for_job_change') }}</textarea>
                        </div>
                    </div>

                    <div class="form-section-title">Education & Resume</div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Education</label>
                            <input type="text" name="education" class="form-control" value="{{ old('education') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Upload Resume (PDF or DOC) *</label>
                            <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button class="btn btn-primary px-5 py-2" type="submit">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
