// server.js
const express = require('express');
const { Liquid } = require('liquidjs');
const fs = require('fs');
const path = require('path');

const app = express();
const port = 3000;
const engine = new Liquid({
    // Allow reading files relative to the template's directory (useful if you have includes)
    root: __dirname,
    extname: '.html' // Optional: Default extension for includes/layouts
});

// Add a 'date' filter similar to Liquid's standard one if needed
engine.registerFilter('date', (input, format) => {
    if (!input) return '';
    // Basic implementation - PDFMonkey might have more complex date handling
    const date = (input === 'now') ? new Date() : new Date(input);
    if (isNaN(date.getTime())) return input; // Return original if date parsing fails

    // Very basic format parser - for robust parsing, consider a library like date-fns or moment
    let formatted = format;
    formatted = formatted.replace('%Y', date.getFullYear());
    formatted = formatted.replace('%m', ('0' + (date.getMonth() + 1)).slice(-2));
    formatted = formatted.replace('%d', ('0' + date.getDate()).slice(-2));
    formatted = formatted.replace('%B', date.toLocaleString('default', { month: 'long' }));
    // Add more format specifiers as needed (%a, %b, %H, %M, %S, etc.)

    return formatted;
});

// Add 'newline_to_br' filter
engine.registerFilter('newline_to_br', (input) => {
  return (input || '').replace(/\r\n|\n|\r/g, '<br />');
});

// Add 'parse_json' filter (simple implementation)
engine.registerFilter('parse_json', (input) => {
    try {
        return JSON.parse(input);
    } catch (e) {
        console.error("Error parsing JSON in filter:", e);
        return {}; // Return empty object on error
    }
});

// Add 'url_encode' filter
engine.registerFilter('url_encode', (input) => {
    return encodeURIComponent(input || '');
});

app.get('/', async (req, res) => {
  try {
    // Read the template file
    const templatePath = path.join(__dirname, 'pdfmonkeytemplate.html');
    const templateContent = fs.readFileSync(templatePath, 'utf8');

    // Read the sample data
    const dataPath = path.join(__dirname, 'sample_data.json');
    const sampleData = JSON.parse(fs.readFileSync(dataPath, 'utf8'));

    // Render the template
    const html = await engine.parseAndRender(templateContent, sampleData);
    res.send(html);

  } catch (error) {
    console.error("Error rendering template:", error);
    // Send a more helpful error message to the browser
    res.status(500).send(`
      <h1>Template Rendering Error</h1>
      <pre>${error.stack || error.message}</pre>
      <h2>Context:</h2>
      <pre>${JSON.stringify(error.context || 'No context available', null, 2)}</pre>
    `);
  }
});

app.listen(port, () => {
  console.log(`Preview server running at http://localhost:${port}`);
  console.log('Ensure pdfmonkeytemplate.html and sample_data.json are in the same directory.');
  console.log('Run `npm install express liquidjs` if you haven\'t already.');
}); 