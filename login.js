document.addEventListener("DOMContentLoaded", () => {

    const card = document.querySelector(".card");
    const canvas = document.getElementById("bg");
    const ctx = canvas.getContext("2d");

    // ===== CANVAS SIZE FIX =====
    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resizeCanvas();
    window.addEventListener("resize", resizeCanvas);

    // ===== 3D CARD EFFECT =====
    document.addEventListener("mousemove", (e) => {
        if(!card) return;
        const x = (window.innerWidth / 2 - e.pageX) / 25;
        const y = (window.innerHeight / 2 - e.pageY) / 25;
        card.style.transform = `rotateY(${x}deg) rotateX(${y}deg)`;
    });

    // ===== PASSWORD TOGGLE SAFE =====
    const passwordInput = document.getElementById("password");
    if(passwordInput){
        const toggle = document.createElement("span");
        toggle.textContent = " Show";
        toggle.style.cursor = "pointer";
        toggle.style.color = "#00fff7";
        passwordInput.parentNode.appendChild(toggle);

        toggle.onclick = () => {
            passwordInput.type =
                passwordInput.type === "password" ? "text" : "password";
        };
    }

    // ===== PARTICLES =====
    const particles = [];
    const colors = ["#ff00ff","#00fff7","#fffb00"];

    class Particle {
        constructor(){
            this.x = Math.random()*canvas.width;
            this.y = Math.random()*canvas.height;
            this.size = Math.random()*3+1;
            this.speedX = Math.random()*1-0.5;
            this.speedY = Math.random()*1-0.5;
            this.color = colors[Math.floor(Math.random()*colors.length)];
        }
        update(){
            this.x+=this.speedX;
            this.y+=this.speedY;
            if(this.x<0||this.x>canvas.width) this.speedX*=-1;
            if(this.y<0||this.y>canvas.height) this.speedY*=-1;
        }
        draw(){
            ctx.fillStyle=this.color;
            ctx.beginPath();
            ctx.arc(this.x,this.y,this.size,0,Math.PI*2);
            ctx.fill();
        }
    }

    function init(){
        particles.length = 0;
        for(let i=0;i<100;i++) particles.push(new Particle());
    }

    function animate(){
        ctx.clearRect(0,0,canvas.width,canvas.height);
        particles.forEach(p=>{p.update();p.draw();});
        requestAnimationFrame(animate);
    }

    init();
    animate();
});

