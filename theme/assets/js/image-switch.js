document.addEventListener('DOMContentLoaded', function () {
	const mainImage = document.querySelector('.main-image');
	const mainLink = document.querySelector('.main-image-link');

	document.querySelectorAll('.thumb-link').forEach(link => {
		link.addEventListener('click', function (e) {
			e.preventDefault();

			const newSrc = this.getAttribute('data-large');
			const newHref = this.getAttribute('data-full');

			mainImage.src = newSrc;
			mainLink.href = newHref;
		});
	});
});