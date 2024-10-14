<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom PDF Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        #pdf-viewer {
            position: relative;
            display: inline-block;
        }
        #pdf-canvas {
            border: 1px solid #ccc;
            width: 100%;
            max-width: 600px;
            background-color: #fff;
        }
        .btn {
            padding: 10px 16px;
            margin: 5px;
            color: #fff;
            background-color: #FFA500;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-green {
            background-color: #32CD32;
        }
        #annotation-layer {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
        }
        #highlight-color {
            margin-left: 10px;
            padding: 5px;
            font-size: 14px;
        }
        #text-input {
            display: none;
            position: absolute;
            border: 1px solid #ccc;
            font-size: 14px;
            padding: 2px;
            outline: none;
        }
    </style>
</head>
<body>
    <h1>Custom PDF Viewer</h1>
    <div id="pdf-viewer">
        <canvas id="pdf-canvas"></canvas>
        <canvas id="annotation-layer"></canvas>
    </div>

    <div class="button-group">
        <button id="prev" class="btn">&#x25C0; Previous</button>
        <button id="next" class="btn">Next &#x25B6;</button>
        <button id="zoom-in" class="btn">Zoom In</button>
        <button id="zoom-out" class="btn">Zoom Out</button>
        <button id="fit-page" class="btn">Fit to Page</button>
        <button id="scribble" class="btn btn-green">Scribble</button>
        <button id="text" class="btn btn-green">Add Text</button>
        <button id="highlight" class="btn btn-green">Highlight</button>
        <select id="highlight-color">
            <option value="rgba(255, 255, 0, 0.5)">Yellow</option>
            <option value="rgba(0, 255, 0, 0.5)">Green</option>
            <option value="rgba(255, 0, 0, 0.5)">Red</option>
            <option value="rgba(0, 0, 255, 0.5)">Blue</option>
        </select>
        <button id="save" class="btn">Save</button>
    </div>

    <input type="text" id="text-input">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.min.js"></script>
    <script>
        const pdfUrl = "{{ asset('storage/pdfs/' . $filename) }}";
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.worker.min.js';

        let pdfDoc = null, pageNum = 1, scale = 1.5, isDrawing = false;
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

        // Highlight functionality
        document.getElementById('highlight').addEventListener('click', () => {
            annotationCtx.fillStyle = document.getElementById('highlight-color').value;
            canvas.addEventListener('mousedown', (e) => {
                const startX = e.offsetX, startY = e.offsetY;
                const onMouseMove = (ev) => {
                    annotationCtx.clearRect(0, 0, annotationCanvas.width, annotationCanvas.height);
                    annotationCtx.fillRect(startX, startY, ev.offsetX - startX, ev.offsetY - startY);
                };
                const onMouseUp = () => {
                    canvas.removeEventListener('mousemove', onMouseMove);
                    canvas.removeEventListener('mouseup', onMouseUp);
                };
                canvas.addEventListener('mousemove', onMouseMove);
                canvas.addEventListener('mouseup', onMouseUp, { once: true });
            });
        });

        // Scribble functionality
        document.getElementById('scribble').addEventListener('click', () => {
            annotationCanvas.style.pointerEvents = 'auto';
            annotationCtx.strokeStyle = '#000000';
            annotationCtx.lineWidth = 2;
            annotationCtx.lineCap = 'round';
            annotationCanvas.addEventListener('mousedown', (e) => {
                isDrawing = true;
                annotationCtx.beginPath();
                annotationCtx.moveTo(e.offsetX, e.offsetY);
            });
            annotationCanvas.addEventListener('mousemove', (e) => {
                if (!isDrawing) return;
                annotationCtx.lineTo(e.offsetX, e.offsetY);
                annotationCtx.stroke();
            });
            annotationCanvas.addEventListener('mouseup', () => {
                isDrawing = false;
            });
            annotationCanvas.addEventListener('mouseleave', () => {
                isDrawing = false;
            });
        });

        // Text functionality
        document.getElementById('text').addEventListener('click', () => {
            canvas.addEventListener('click', (e) => {
                const x = e.offsetX, y = e.offsetY;
                textInput.style.display = 'block';
                textInput.style.left = `${pdfViewer.offsetLeft + x}px`;
                textInput.style.top = `${pdfViewer.offsetTop + y}px`;
                textInput.focus();
                textInput.onblur = () => {
                    annotationCtx.font = '14px Arial';
                    annotationCtx.fillText(textInput.value, x, y);
                    textInput.style.display = 'none';
                    textInput.value = '';
                };
            }, { once: true });
        });

        pdfjsLib.getDocument(pdfUrl).promise.then((doc) => {
            pdfDoc = doc;
            renderPage(pageNum);
        });
    </script>
</body>
</html>
