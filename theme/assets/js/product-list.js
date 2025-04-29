function equalizeProductHeights() {
	const items = document.querySelectorAll('ul.products li');
	if (!items.length) return;
	
	const anchors = Array.from(items)
		.map(li => li.querySelector('a'))
		.filter(Boolean);
	
	let maxHeight = 0;
	anchors.forEach(anchor => {
		anchor.style.height = 'auto'; // Reset 'em to default before measuring.
		const height = anchor.parentElement.clientHeight;
		if (height > maxHeight) maxHeight = height;
	});

	const targetHeight = maxHeight * 0.9 + 'px';
	anchors.forEach(
		anchor => anchor.style.height = targetHeight
	);
}

equalizeProductHeights();

window.addEventListener('resize', equalizeProductHeights);