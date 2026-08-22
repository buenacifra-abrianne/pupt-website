(function() {
    if (window.PUPParticlesInitialized) return;
    window.PUPParticlesInitialized = true;

    document.addEventListener("DOMContentLoaded", function() {
        const container = document.querySelector(".hero-shell") || document.querySelector(".menu");
        if (!container) return;

        const canvas = document.getElementById("hero-particles");
        if (!canvas) return;

        const ctx = canvas.getContext("2d");
        
        // Configuration
        const spacing = 20; // Tighter grid spacing (more dots)
        const mouseRadius = 120; // Interaction radius
        const pushForce = 5; // How hard they are pushed
        const springForce = 0.1; // How fast they snap back to the grid
        const friction = 0.85; // How much they slow down
        
        let particles = [];
        let mouse = {
            x: -1000,
            y: -1000,
            isActive: false
        };

        // Resize canvas to fill the hero shell
        function resize() {
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;
            initParticles(); 
        }

        window.addEventListener('resize', resize);
        
        // Track mouse over the container section
        container.addEventListener('mousemove', function(e) {
            const rect = canvas.getBoundingClientRect();
            mouse.x = e.clientX - rect.left;
            mouse.y = e.clientY - rect.top;
            mouse.isActive = true;
        });

        container.addEventListener('mouseleave', function() {
            mouse.isActive = false;
            // Move mouse offscreen so particles snap back
            mouse.x = -1000;
            mouse.y = -1000;
        });

        class Particle {
            constructor(x, y) {
                // Current position
                this.x = x;
                this.y = y;
                // Original grid position to spring back to
                this.originX = x;
                this.originY = y;
                // Velocity
                this.vx = 0;
                this.vy = 0;
                
                // Size of the grid dot (smaller)
                this.size = 0.8;
                // Color of the grid dot (semi-transparent white)
                this.color = 'rgba(255, 255, 255, 0.3)';
            }

            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = this.color;
                ctx.fill();
            }

            update() {
                // Check mouse interaction
                if (mouse.isActive || mouse.x > -1000) {
                    let dx = mouse.x - this.x;
                    let dy = mouse.y - this.y;
                    let distance = Math.sqrt(dx * dx + dy * dy);
                    
                    if (distance < mouseRadius) {
                        const angle = Math.atan2(dy, dx);
                        const forceDirectionX = Math.cos(angle);
                        const forceDirectionY = Math.sin(angle);
                        const force = (mouseRadius - distance) / mouseRadius; 
                        
                        // Push away from mouse
                        this.vx -= forceDirectionX * force * pushForce;
                        this.vy -= forceDirectionY * force * pushForce;
                    }
                }
                
                // Spring force pulling back to original grid position
                this.vx += (this.originX - this.x) * springForce;
                this.vy += (this.originY - this.y) * springForce;
                
                // Apply friction
                this.vx *= friction;
                this.vy *= friction;
                
                // Update actual position
                this.x += this.vx;
                this.y += this.vy;
            }
        }

        function initParticles() {
            particles = [];
            
            // Calculate how many columns and rows we need
            const cols = Math.floor(canvas.width / spacing) + 1;
            const rows = Math.floor(canvas.height / spacing) + 1;
            
            // Center the grid by calculating the offset
            const offsetX = (canvas.width - ((cols - 1) * spacing)) / 2;
            const offsetY = (canvas.height - ((rows - 1) * spacing)) / 2;

            for (let i = 0; i < cols; i++) {
                for (let j = 0; j < rows; j++) {
                    const x = i * spacing + offsetX;
                    const y = j * spacing + offsetY;
                    particles.push(new Particle(x, y));
                }
            }
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < particles.length; i++) {
                particles[i].draw();
                particles[i].update();
            }
            requestAnimationFrame(animate);
        }

        // Init
        resize();
        animate();
    });
})();
