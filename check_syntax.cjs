const fs = require('fs');
const cp = require('child_process');

['academics', 'about', 'events', 'research', 'students'].forEach(name => {
    const file = `resources/views/partials/${name}_cms_preview_editor.blade.php`;
    const content = fs.readFileSync(file, 'utf-8');
    const scriptRegex = /<script.*?>([\s\S]*?)<\/script>/g;
    let match;
    let index = 0;
    while ((match = scriptRegex.exec(content)) !== null) {
        index++;
        const js = match[1];
        // Strip out simple blade variables {{ $var }} to avoid breaking syntax check
        let cleanedJs = js.replace(/\{\{\s*\$[^}]+\}\}/g, '""');
        // Strip out @if / @endif
        cleanedJs = cleanedJs.replace(/@if.*?$/gm, 'if(true){');
        cleanedJs = cleanedJs.replace(/@elseif.*?$/gm, '}else if(true){');
        cleanedJs = cleanedJs.replace(/@else.*?$/gm, '}else{');
        cleanedJs = cleanedJs.replace(/@endif.*?$/gm, '}');
        cleanedJs = cleanedJs.replace(/@foreach.*?$/gm, 'for(let i=0;i<1;i++){');
        cleanedJs = cleanedJs.replace(/@endforeach.*?$/gm, '}');
        
        const tmpFile = `temp_${name}_${index}.js`;
        fs.writeFileSync(tmpFile, cleanedJs);
        try {
            cp.execSync(`node -c ${tmpFile}`, { stdio: 'pipe' });
        } catch (e) {
            console.log(`\nSyntax Error in ${file}:`);
            console.log(e.stderr.toString());
        }
    }
});
