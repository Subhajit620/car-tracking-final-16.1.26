const ownerPic = document.getElementById("ownerPic");
const previewPic = document.getElementById("previewPic");
const form = document.getElementById("ownerForm");
const successMsg = document.getElementById("successMsg");
const container = document.querySelector(".container");

/* IMAGE PREVIEW */
ownerPic.addEventListener("change", () => {
    const file = ownerPic.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = e => {
            previewPic.src = e.target.result;
            previewPic.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
});

/* FORM SUBMIT */
form.addEventListener("submit", e => {
    e.preventDefault();
    successMsg.style.display = "block";
});

/* REAL 3D MOUSE TRACKING */
container.addEventListener("mousemove", e => {
    const rect = container.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    const rotateX = ((y - centerY)/centerY) * 10;
    const rotateY = ((x - centerX)/centerX) * 10;

    container.style.transform = `
        perspective(1200px)
        rotateX(${-rotateX}deg)
        rotateY(${rotateY}deg)
        scale(1.03)
    `;
});

container.addEventListener("mouseleave", () => {
    container.style.transition="transform .6s ease";
    container.style.transform=`perspective(1200px) rotateX(0deg) rotateY(0deg) scale(1)`;
    setTimeout(()=>container.style.transition="",600);
});