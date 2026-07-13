const fs = require('fs');

function checkFile(file) {
    const content = fs.readFileSync(file, 'utf-8');
    const scriptRegex = /<script.*?>([\s\S]*?)<\/script>/g;
    let match;
    let index = 0;
    while ((match = scriptRegex.exec(content)) !== null) {
        index++;
        const js = match[1];
        try {
            // Using Function constructor to quickly detect syntax errors
            new Function(js);
        } catch (e) {
            console.error(`Syntax Error in ${file} (Script #${index}):`, e.message);
        }
    }
}

['academics', 'about', 'events', 'research', 'students'].forEach(name => {
    checkFile(`resources/views/partials/${name}_cms_preview_editor.blade.php`);
});
console.log('Syntax check complete.');
