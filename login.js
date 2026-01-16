document.addEventListener("DOMContentLoaded", () => {
    const card = document.querySelector(".card");

    // 3D card rotation effect
    document.addEventListener("mousemove", (e) => {
        const x = (window.innerWidth / 2 - e.pageX) / 25;
        const y = (window.innerHeight / 2 - e.pageY) / 25;
        card.style.transform = `rotateY(${x}deg) rotateX(${y}deg)`;
    });

    document.addEventListener("mouseleave", () => {
        card.style.transform = `rotateY(0deg) rotateX(0deg)`;
    });

    // Password toggle
    const passwordInput = document.getElementById("password");
    const togglePassword = document.createElement("span");
    togglePassword.textContent = "Show";
    togglePassword.style.cursor = "pointer";
    togglePassword.style.marginLeft = "10px";
    togglePassword.style.color = "#00fff7";
    passwordInput.parentNode.appendChild(togglePassword);

    togglePassword.addEventListener("click", () => {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            togglePassword.textContent = "Hide";
        } else {
            passwordInput.type = "password";
            togglePassword.textContent = "Show";
        }
    });

    // Simple form validation
    const form = document.querySelector("form");
    form.addEventListener("submit", (e) => {
        const email = document.getElementById("email").value.trim();
        const password = passwordInput.value.trim();

        if (!email || !password) {
            alert("Please fill in all fields.");
            e.preventDefault();
        }
    });

    // Particles background
    const canvas = document.getElementById("bg");
    const ctx = canvas.getContext("2d");
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const particlesArray = [];
    const colors = ["#ff00ff", "#00fff7", "#fffb00"];

    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 3 + 1;
            this.speedX = Math.random() * 1 - 0.5;
            this.speedY = Math.random() * 1 - 0.5;
            this.color = colors[Math.floor(Math.random() * colors.length)];
        }

        update() {
            this.x += this.speedX;
            this.y += this.speedY;

            if(this.x < 0 || this.x > canvas.width) this.speedX *= -1;
            if(this.y < 0 || this.y > canvas.height) this.speedY *= -1;
        }

        draw() {
            ctx.fillStyle = this.color;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function initParticles() {
        for(let i=0; i<100; i++){
            particlesArray.push(new Particle());
        }
    }

    function animateParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        particlesArray.forEach(p => {
            p.update();
            p.draw();
        });
        requestAnimationFrame(animateParticles);
    }

    initParticles();
    animateParticles();

    window.addEventListener("resize", () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
});
