<form action="/generate-pdf" method="POST">
    @csrf
    <div>
        <label for="text">Text:</label>
        <textarea id="text" name="text" rows="4" cols="50">Enter your text here...</textarea>
    </div>
    <div>
        <label for="font_size">Font Size:</label>
        <input type="number" id="font_size" name="font_size" value="12">
    </div>
    <div>
        <label for="font_family">Font Family:</label>
        <select id="font_family" name="font_family">
            <option value="helvetica">Helvetica</option>
            <option value="times">Times</option>
            <option value="courier">Courier</option>
        </select>
    </div>
    <button type="submit">Generate PDF</button>
</form>
