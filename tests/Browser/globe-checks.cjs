const assert = require('node:assert/strict');

module.exports = async ({ evaluate }) => {
    for (let attempt = 0; attempt < 30; attempt++) {
        if (await evaluate(`!!document.querySelector('[data-globe-ready], .magicui-globe-fallback')`)) break;
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    if (process.env.GLOBE_FALLBACK === '1') {
        assert.equal(await evaluate(`!!document.querySelector('.magicui-globe-fallback')`), true, 'No-WebGL fallback renders');
    } else {
        assert.deepEqual(await evaluate(`(() => {
            const canvas=document.querySelector('#landing-globe canvas'),errors=[];
            if(!canvas||canvas.dataset.globeReady!=='true')return ['Globe did not initialize'];
            const r=canvas.getBoundingClientRect();
            if(r.width<279||r.width>441||Math.abs(r.width-r.height)>1)errors.push('Globe must stay within the overlay size');
            if(!(canvas.getContext('webgl2')||canvas.getContext('webgl')||canvas.getContext('experimental-webgl')))errors.push('WebGL renderer is unavailable');
            if(canvas.tabIndex!==-1)errors.push('Decorative globe must not add a keyboard stop');
            return errors;
        })()`), [], 'Globe rendering and accessibility');
        const frame=()=>evaluate(`new Promise(resolve=>requestAnimationFrame(()=>{
            const canvas=document.querySelector('#landing-globe canvas'),gl=canvas.getContext('webgl2')||canvas.getContext('webgl')||canvas.getContext('experimental-webgl');
            const pixels=new Uint8Array(64*64*4);
            gl.readPixels(Math.floor(canvas.width/2)-32,Math.floor(canvas.height/2)-32,64,64,gl.RGBA,gl.UNSIGNED_BYTE,pixels);
            let red=0,sum=0;
            for(let i=0;i<pixels.length;i+=4){
                if(pixels[i]>pixels[i+1]*1.2&&pixels[i]>pixels[i+2]*1.2&&pixels[i+3]>0)red++;
                sum+=pixels[i]+pixels[i+1]+pixels[i+2];
            }
            resolve({red,sum});
        }))`);
        await new Promise(resolve => setTimeout(resolve, 400));
        const before = await frame();
        assert.ok(before.red > 50, 'Globe must actually draw red pixels');
        if (process.env.REDUCED_MOTION === '1') {
            await new Promise(resolve => setTimeout(resolve, 150));
            assert.equal((await frame()).sum, before.sum, 'Reduced motion stops automatic rotation');
        }
    }
    assert.deepEqual(await evaluate(`(() => {
        const errors=[],host=document.getElementById('landing-globe'),panel=host.closest('.landing-actions'),container=document.querySelector('.landing-container');
        const r=host.getBoundingClientRect(),pr=panel.getBoundingClientRect(),height=container.getBoundingClientRect().height;
        if(getComputedStyle(host).position!=='absolute')errors.push('Globe must be outside the layout flow');
        if(Math.abs(r.left+r.width/2-pr.right)>1)errors.push('Right edge must crop the globe in half');
        if(host.getAttribute('aria-hidden')!=='true'||!host.inert||getComputedStyle(host).pointerEvents!=='none')errors.push('Decorative overlay must not intercept input');
        host.style.display='none';const without=container.getBoundingClientRect().height;host.style.display='';
        if(Math.abs(height-without)>1)errors.push('Globe increases container height');
        const link=document.querySelector('a.portal-button--homepage');link.scrollIntoView({block:'center',behavior:'instant'});
        const lr=link.getBoundingClientRect(),target=document.elementFromPoint(lr.left+lr.width/2,lr.top+lr.height/2);
        if(!target?.closest('a.portal-button--homepage'))errors.push('Overlay blocks landing destination');
        return errors;
    })()`),[],'Half-globe overlay does not affect layout or controls');
    assert.equal(await evaluate(`document.querySelector('a.portal-button--homepage')?.getClientRects().length>0`), true, 'Landing destination remains available');
};
