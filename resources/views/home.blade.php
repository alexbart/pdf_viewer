@extends('layouts.app')

@section('content')
    <div class="container-fluid text-center py-5" style="background-color: #f8f9fa;">
        <h1 class="display-4 mb-4" style="color: #007bff;">Welcome to PDF_VIEWER!</h1>
        <p class="lead text-muted">
            Your simple, efficient, and secure solution for viewing and editing PDFs directly in your browser.
            Upload your PDFs, view them with ease, edit and access your documents anytime, anywhere!
        </p>
        <p class="text-muted mb-4">
            Ready to get started? Click the button below to upload your PDF and start viewing right away.
        </p>
        <a href="{{ route('pdf.upload') }}" class="btn btn-primary btn-lg">Get Started</a>

    </div>

     <!-- Add an illustration or icon for a friendly feel -->
@endsection
