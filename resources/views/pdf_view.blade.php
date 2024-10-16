<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Custom PDF Viewer</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa; /* Light background for the page */
            margin: 0;
            padding: 0; /* Remove default padding */
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px; /* Add padding if needed */
        }

        #pdf-viewer {
            position: relative;
            display: block;
            margin-bottom: 20px; /* Space below the viewer */
        }

        #pdf-canvas {
            border: 1px solid #ccc;
            background-color: #fff;
            width: 100%; /* Full width */
            height: auto; /* Maintain aspect ratio */
        }

        .btn-custom {
            padding: 5px 10px; /* Smaller padding for buttons */
            margin: 2px; /* Smaller margin between buttons */
            color: #fff;
            background-color: #FFA500; /* Orange color */
            border: none;
            border-radius: 5px;
            font-size: 12px; /* Smaller font size */
        }

        .btn-custom-green {
            background-color: #32CD32; /* Green color */
        }

        #annotation-layer {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
        }

        #text-input {
            display: none;
            position: absolute;
            border: 1px solid #ccc;
            font-size: 14px;
            padding: 2px;
            outline: none;
            background-color: white;
            color: black;
            z-index: 100;
        }

        /* Centering buttons horizontally */
        .button-group {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            background-color: #e9ecef; /* Light grey background */
            padding: 5px; /* Smaller padding for button group */
            border-radius: 5px;
            margin-bottom: 20px; /* Space between buttons and viewer */
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center">Custom PDF Viewer</h1>
        <div class="button-group">
            <button id="prev" class="btn btn-custom">&#x25C0; Prev</button>
            <button id="next" class="btn btn-custom">Next &#x25B6;</button>
            <button id="zoom-in" class="btn btn-custom">Zoom In</button>
            <button id="zoom-out" class="btn btn-custom">Zoom Out</button>
            <button id="fit-page" class="btn btn-custom">Fit</button>
            <button id="scribble" class="btn btn-custom btn-custom-green">Scribble</button>
            <button id="erase" class="btn btn-custom btn-custom-green">Erase</button>
            <button id="text" class="btn btn-custom btn-custom-green">Text</button>
            <select id="font-selection" class="form-control form-control-sm" style="width: auto;">
                <option value="Arial">Arial</option>
                <option value="Times New Roman">Times New Roman</option>
                <option value="Courier New">Courier New</option>
            </select>
            <button id="highlight" class="btn btn-custom btn-custom-green">Highlight</button>
            <select id="highlight-color" class="form-control form-control-sm" style="width: auto;">
                <option value="rgba(255, 255, 0, 0.5)">Yellow</option>
                <option value="rgba(0, 255, 0, 0.5)">Green</option>
                <option value="rgba(255, 0, 0, 0.5)">Red</option>
                <option value="rgba(0, 0, 255, 0.5)">Blue</option>
            </select>
            <button id="save" class="btn btn-custom">Save</button>
        </div>

        <div id="pdf-viewer">
            <canvas id="pdf-canvas"></canvas>
            <canvas id="annotation-layer"></canvas>
        </div>

        <input type="text" id="text-input">
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.min.js"></script>
    <script>
        const pdfUrl = "{{ asset('storage/pdfs/' . $filename) }}";
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.worker.min.js';

        let pdfDoc = null,
            pageNum = 1,
            scale = 1.5,
            isDrawing = false;
        let activeTool = null;
        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');
        const annotationCanvas = document.getElementById('annotation-layer');
        const annotationCtx = annotationCanvas.getContext('2d');
        const textInput = document.getElementById('text-input');
        const pdfViewer = document.getElementById('pdf-viewer');

        const renderPage = (num) => {
            pdfDoc.getPage(num).then((page) => {
                const viewport = page.getViewport({ scale });
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                annotationCanvas.width = viewport.width;
                annotationCanvas.height = viewport.height;
                page.render({ canvasContext: ctx, viewport });
            });
        };

        const resetListeners = () => {
            annotationCanvas.onmousedown = null;
            annotationCanvas.onmousemove = null;
            annotationCanvas.onmouseup = null;
            annotationCanvas.onclick = null;
        };

        document.getElementById('prev').addEventListener('click', () => {
            if (pageNum > 1) {
                pageNum--;
                renderPage(pageNum);
            }
        });

        document.getElementById('next').addEventListener('click', () => {
            if (pageNum < pdfDoc.numPages) {
                pageNum++;
                renderPage(pageNum);
            }
        });

        document.getElementById('zoom-in').addEventListener('click', () => {
            scale += 0.1;
            renderPage(pageNum);
        });

        document.getElementById('zoom-out').addEventListener('click', () => {
            if (scale > 0.2) {
                scale -= 0.1;
                renderPage(pageNum);
            }
        });

        document.getElementById('fit-page').addEventListener('click', () => {
            scale = 1.5;
            renderPage(pageNum);
        });

        const setupHighlightTool = () => {
            resetListeners();
            annotationCtx.fillStyle = document.getElementById('highlight-color').value;
            activeTool = 'highlight';
            annotationCanvas.onmousedown = (e) => {
                const startX = e.offsetX;
                const startY = e.offsetY;
                annotationCanvas.onmousemove = (ev) => {
                    annotationCtx.clearRect(0, 0, annotationCanvas.width, annotationCanvas.height); // Clear previous highlights
                    annotationCtx.fillRect(startX, startY, ev.offsetX - startX, ev.offsetY - startY); // Draw new highlight
                };
                annotationCanvas.onmouseup = () => {
                    annotationCanvas.onmousemove = null; // Stop drawing on mouse up
                    const imgData = annotationCtx.getImageData(0, 0, annotationCanvas.width, annotationCanvas.height);
                    annotationCtx.putImageData(imgData, 0, 0); // Redraw previous highlights
                };
            };
        };

        document.getElementById('highlight').addEventListener('click', setupHighlightTool);

        const setupScribbleTool = () => {
            resetListeners();
            annotationCtx.strokeStyle = '#000000';
            annotationCtx.lineWidth = 2;
            annotationCtx.lineCap = 'round';
            annotationCanvas.style.pointerEvents = 'auto';
            annotationCanvas.onmousedown = (e) => {
                annotationCtx.beginPath();
                annotationCtx.moveTo(e.offsetX, e.offsetY);
                annotationCanvas.onmousemove = (ev) => {
                    annotationCtx.lineTo(ev.offsetX, ev.offsetY);
                    annotationCtx.stroke();
                };
                annotationCanvas.onmouseup = () => {
                    annotationCanvas.onmousemove = null;
                };
            };
        };

        document.getElementById('scribble').addEventListener('click', () => {
            resetListeners();
            setupScribbleTool();
        });

        const setupTextTool = () => {
            resetListeners();
            activeTool = 'text';
            annotationCanvas.onclick = (e) => {
                const x = e.offsetX;
                const y = e.offsetY;

                // Position the text input where the mouse is clicked
                textInput.style.display = 'block';
                textInput.style.left = `${x}px`;
                textInput.style.top = `${y}px`;
                textInput.value = ''; // Clear previous input
                textInput.focus();

                textInput.onblur = () => {
                    const selectedFont = document.getElementById('font-selection').value;
                    annotationCtx.font = `14px ${selectedFont}`;
                    annotationCtx.fillStyle = 'black'; // Set text color to black
                    annotationCtx.fillText(textInput.value, x, y);
                    textInput.style.display = 'none'; // Hide the input after entering text
                };
            };
        };

        document.getElementById('text').addEventListener('click', setupTextTool);

        document.getElementById('erase').addEventListener('click', () => {
            resetListeners();
            annotationCtx.clearRect(0, 0, annotationCanvas.width, annotationCanvas.height); // Clear annotations
        });

        // Function to save the annotated PDF
        document.getElementById('save').addEventListener('click', () => {
            const combinedCanvas = document.createElement('canvas');
            combinedCanvas.width = canvas.width;
            combinedCanvas.height = canvas.height;

            const combinedCtx = combinedCanvas.getContext('2d');
            combinedCtx.drawImage(canvas, 0, 0);
            combinedCtx.drawImage(annotationCanvas, 0, 0);

            combinedCanvas.toBlob((blob) => {
                const pdf = new jspdf.jsPDF();
                pdf.addImage(combinedCanvas.toDataURL('image/png'), 'PNG', 0, 0, 210, 297); // A4 size conversion
                pdf.save(`annotated_page_${pageNum}.pdf`);
            });
        });

        pdfjsLib.getDocument(pdfUrl).promise.then((doc) => {
            pdfDoc = doc;
            renderPage(pageNum);
        }).catch((error) => {
            alert("Error loading PDF: " + error.message);
        });
    </script>
</body>

</html>
