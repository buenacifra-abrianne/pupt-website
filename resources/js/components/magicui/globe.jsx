// Adapted from https://v3.magicui.design/r/globe (Magic UI, MIT; see LICENSE.md).
import React, { useEffect, useRef, useState } from 'react';
import createGlobe from 'cobe';
import { useMotionValue, useReducedMotion, useSpring } from 'motion/react';

const GLOBE_CONFIG = {
    width: 800,
    height: 800,
    devicePixelRatio: 2,
    phi: 2.3,
    theta: 0.3,
    dark: 0,
    diffuse: 0.4,
    mapSamples: 16000,
    mapBrightness: 1.2,
    baseColor: [0.95, 0.08, 0.12],
    markerColor: [1, 0.55, 0.45],
    glowColor: [1, 0.04, 0.08],
    markers: [
        { location: [14.5995, 120.9842], size: 0.03 },
        { location: [19.076, 72.8777], size: 0.1 },
        { location: [23.8103, 90.4125], size: 0.05 },
        { location: [30.0444, 31.2357], size: 0.07 },
        { location: [39.9042, 116.4074], size: 0.08 },
        { location: [-23.5505, -46.6333], size: 0.1 },
        { location: [19.4326, -99.1332], size: 0.1 },
        { location: [40.7128, -74.006], size: 0.1 },
        { location: [34.6937, 135.5022], size: 0.05 },
        { location: [41.0082, 28.9784], size: 0.06 },
    ],
};

export function Globe({ className = '', config = GLOBE_CONFIG, decorative = false }) {
    const canvasRef = useRef(null);
    const pointer = useRef(null);
    const rotation = useMotionValue(0);
    const spring = useSpring(rotation, { mass: 1, damping: 30, stiffness: 100 });
    const reducedMotion = useReducedMotion();
    const [available, setAvailable] = useState(true);

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        let phi = config.phi;
        let width = canvas.offsetWidth;
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        const resize = new ResizeObserver(() => { width = canvas.offsetWidth; });
        let globe;
        try {
            globe = createGlobe(canvas, {
                ...config,
                devicePixelRatio: ratio,
                width: width * ratio,
                height: width * ratio,
                onRender: (state) => {
                    if (pointer.current === null && !reducedMotion && !document.hidden) phi += 0.005;
                    state.phi = phi + spring.get();
                    state.width = width * ratio;
                    state.height = width * ratio;
                },
            });
            resize.observe(canvas);
            canvas.dataset.globeReady = 'true';
        } catch {
            // Leave the landing links usable on devices without WebGL.
            setAvailable(false);
        }
        return () => { resize.disconnect(); globe?.destroy(); };
    }, [config, spring, reducedMotion]);

    return (
        <div className={`magicui-globe ${className}`}>
            {available ? (
                <canvas
                    ref={canvasRef}
                    tabIndex={decorative ? -1 : 0}
                    role={decorative ? undefined : 'img'}
                    aria-label={decorative ? undefined : 'Red world globe. Drag or use the left and right arrow keys to rotate.'}
                    onPointerDown={(event) => {
                        pointer.current = event.clientX;
                        event.currentTarget.setPointerCapture(event.pointerId);
                        event.currentTarget.style.cursor = 'grabbing';
                    }}
                    onPointerMove={(event) => {
                        if (pointer.current === null) return;
                        rotation.set(rotation.get() + (event.clientX - pointer.current) / 1400);
                        pointer.current = event.clientX;
                    }}
                    onPointerUp={(event) => {
                        pointer.current = null;
                        event.currentTarget.style.cursor = 'grab';
                        if (event.currentTarget.hasPointerCapture(event.pointerId)) {
                            event.currentTarget.releasePointerCapture(event.pointerId);
                        }
                    }}
                    onLostPointerCapture={(event) => {
                        pointer.current = null;
                        event.currentTarget.style.cursor = 'grab';
                    }}
                    onKeyDown={(event) => {
                        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
                        event.preventDefault();
                        rotation.set(rotation.get() + (event.key === 'ArrowLeft' ? -0.2 : 0.2));
                    }}
                />
            ) : <div className="magicui-globe-fallback" role="img" aria-label="Red globe" />}
        </div>
    );
}
