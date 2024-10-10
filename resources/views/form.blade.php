<!-- resources/views/form.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Interactive Form with PDF Upload</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10">
        <div class="max-w-md mx-auto bg-white p-5 rounded shadow-md">
            <h2 class="text-2xl font-bold mb-5">Welcome to PDF-Viewer!</h2>

            <!-- Display success message -->
            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Display validation errors -->
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-2 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/form" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-gray-700">Name</label>
                    <input type="text" id="name" name="name" class="w-full border rounded p-2" value="{{ old('name') }}">
                </div>
                <div>
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="w-full border rounded p-2" value="{{ old('email') }}">
                </div>
                <div>
                    <label for="message" class="block text-gray-700">Notes</label>
                    <textarea id="message" name="message" class="w-full border rounded p-2">{{ old('message') }}</textarea>
                </div>
                <div>
                    <label for="pdf_file" class="block text-gray-700">Upload PDF</label>
                    <input type="file" id="pdf_file" name="pdf_file" class="w-full border rounded p-2" accept="application/pdf">
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>
