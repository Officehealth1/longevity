const fs = require('fs').promises;
const path = require('path');
const { Liquid } = require('liquidjs');

const templateFile = path.join(__dirname, 'pdfmonkeytemplate.html');
const dataFile = path.join(__dirname, 'sample_data.json');
const outputFile = path.join(__dirname, 'rendered_report.html');

const engine = new Liquid({
    root: __dirname, // specifies the directory for includes/layouts
    extname: '.html' // allows referencing templates without extension
});

async function renderTemplate() {
    try {
        console.log(`Reading template: ${templateFile}`);
        const templateContent = await fs.readFile(templateFile, 'utf8');

        console.log(`Reading data: ${dataFile}`);
        const dataContent = await fs.readFile(dataFile, 'utf8');
        const data = JSON.parse(dataContent);

        // Register custom filters if needed (example)
        // engine.registerFilter('my_filter', (input) => input.toUpperCase());

        console.log('Rendering template...');
        const renderedHtml = await engine.parseAndRender(templateContent, data);

        console.log(`Writing output: ${outputFile}`);
        await fs.writeFile(outputFile, renderedHtml, 'utf8');

        console.log('Successfully rendered report!');

    } catch (error) {
        console.error('Error rendering template:', error);
    }
}

renderTemplate(); 