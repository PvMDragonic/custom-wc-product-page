document.addEventListener('DOMContentLoaded', function () {
    const mainImageWrapper = document.querySelector('.main-image-wrapper');
	if (!mainImageWrapper) return;
	
    const mainImage = mainImageWrapper.querySelector('img');
	if (!mainImage) return;
	
	mainImageWrapper.addEventListener('mousemove', function (e) {
		const rect = mainImageWrapper.getBoundingClientRect();
		const offsetX = e.clientX - rect.left;
		const offsetY = e.clientY - rect.top;
		const percentX = offsetX / rect.width * 100;
		const percentY = offsetY / rect.height * 100;
		
		mainImage.style.transformOrigin = `${percentX}% ${percentY}%`;
    	mainImage.style.transform = "scale(2)";
	});
	
	 mainImageWrapper.addEventListener('mouseleave', function () {
        mainImage.style.transformOrigin = "center center";
        mainImage.style.transform = "scale(1)";
    });
});
