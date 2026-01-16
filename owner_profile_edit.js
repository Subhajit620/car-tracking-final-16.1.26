const card = document.querySelector(".container");

document.addEventListener("mousemove", e => {
    const x = (window.innerWidth/2 - e.pageX)/20;
    const y = (window.innerHeight/2 - e.pageY)/20;
    card.style.transform = `rotateY(${x}deg) rotateX(${y}deg)`;
});

document.addEventListener("mouseleave", ()=>{
    card.style.transform="rotateX(0) rotateY(0)";
});
