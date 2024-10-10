<!-- resources/views/pdf-view.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View PDF</title>
    <style>
        #pdf-container {
            width: 100%;
            height: 800px;
            border: 1px solid #000;
        }
    </style>
    <!-- Include PDF.js and pdfAnnotate.js -->
    <script src="{{ asset('js/pdf.js') }}"></script>
    <script src="{{ asset('js/pdf.worker.js') }}"></script>
    <script src="{{ asset('js/pdf-annotate.js') }}"></script>
</head>
<body>
    <h1>View and Annotate PDF</h1>
    <div id="pdf-container"></div>
    <button onclick="addText()">Add Text</button>
    <button onclick="highlightText()">Highlight</button>
    <button onclick="saveAnnotations()">Save Annotations</button>
    <p><a href="/form">Go back to the form</a></p>

    <script>
        const pdfFile = "{{ asset('storage/pdfs/' . $filename) }}";

        // Load PDF.js and pdfAnnotate to display and annotate the PDF
        PDFAnnotate.setStoreAdapter(new PDFAnnotate.LocalStoreAdapter());

        PDFAnnotate.init({
            documentId: 'pdf-container',
            pdfFileUrl: pdfFile,
            scale: 1.5
        });

        // Function to add text annotation
        function addText() {
            PDFAnnotate.UI.enableEdit();
            PDFAnnotate.UI.addText('Add your text here');
        }

        // Function to highlight text
        function highlightText() {
            PDFAnnotate.UI.enableEdit();
            PDFAnnotate.UI.setPen('yellow', 2);
        }

        // Function to save annotations
        function saveAnnotations() {
            PDFAnnotate.UI.saveAnnotations(function (data) {
                console.log('Annotations saved');
                alert('Annotations saved!');
            });
        }
    </script>
</body>
</html>
