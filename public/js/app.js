// Global variables
let currentStep = 1;
let uploadedImage = null;
let currentImageData = null;

// Animation variables
let animationTimer = null;
let dotCount = 0;
let countdownValue = 15;
let countdownTimer = null;

// -------- Demo simulation --------

const demoBase = "https://sc00p-v01.s3.us-east-1.amazonaws.com/sample-images/";
const demoSamples = [
	{
		url: demoBase + "cat-on-sofa.webp",
		description: "A brown cat relaxing on a sofa",
		content_tags: ["cat", "sofa", "pet"],
		style_tags: ["bright lighting", "indoors"],
		technical_tags: ["photography", "depth of field"],
	},
	{
		url: demoBase + "aerial-pine-forest.webp",
		description: "An aerial view of a dense pine forest",
		content_tags: ["forest", "trees", "nature"],
		style_tags: ["aerial", "natural light"],
		technical_tags: ["drone shot", "high resolution"],
	},
	{
		url: demoBase + "assorted-sushi-plate.webp",
		description: "A plate of assorted sushi on a wooden table",
		content_tags: ["sushi", "food", "plate"],
		style_tags: ["close-up", "colorful"],
		technical_tags: ["macro", "high detail"],
	},
	{
		url: demoBase + "pink-sky-mountains.webp",
		description: "Snow-covered mountains under a pink sky at sunset",
		content_tags: ["mountains", "snow", "sunset"],
		style_tags: ["landscape", "sunset colors"],
		technical_tags: ["long exposure", "high dynamic range"],
	},
	{
		url: demoBase + "modern-workspace.webp",
		description: "A modern workspace with laptop and coffee",
		content_tags: ["laptop", "coffee", "workspace"],
		style_tags: ["minimal", "indoors"],
		technical_tags: ["photography", "shallow depth"],
	},
];

function renderDemoThumbs() {
	const container = document.getElementById("demo-thumbs");
	if (!container) return;
	demoSamples.forEach((s, idx) => {
		const btn = document.createElement("button");
		btn.className = "rounded overflow-hidden shadow hover:shadow-lg transition";
		btn.innerHTML = `<img src="${s.url}" alt="sample ${idx + 1}" class="w-full h-full object-cover" />`;
		btn.addEventListener("click", () => runDemo(idx));
		container.appendChild(btn);
	});
}

function runDemo(index) {
	const sample = demoSamples[index];
	const resultsSection = document.getElementById("demo-results");
	const imgEl = document.getElementById("demo-image");
	const tagsEl = document.getElementById("demo-tags");

	// Reset
	resultsSection.classList.remove("hidden");
	imgEl.src = sample.url;
	tagsEl.innerHTML = '<p class="text-gray-500">Analyzing image<span id="demo-dots"></span></p>';

	// Simulate delay with dot animation
	let dots = 0;
	const dotInt = setInterval(() => {
		dots = (dots % 3) + 1;
		document.getElementById("demo-dots").textContent = ".".repeat(dots);
	}, 500);

	setTimeout(() => {
		clearInterval(dotInt);
		displayDemoResult(sample);
	}, 2000 + Math.random() * 1000);
}

function displayDemoResult(sample) {
	const tagsEl = document.getElementById("demo-tags");
	tagsEl.innerHTML = "";

	// description
	tagsEl.innerHTML += `<p class="section-title mb-2">Description</p><p class="mb-4">${sample.description}</p>`;

	// Tag categories
	const tagTypes = [
		{ key: "content_tags", label: "Content" },
		{ key: "style_tags", label: "Style" },
		{ key: "technical_tags", label: "Technical" },
	];

	tagTypes.forEach(({ key, label }) => {
		if (sample[key]) {
			tagsEl.innerHTML += `<p class="section-title mb-1">${label} tags</p>`;
			const wrap = document.createElement("div");
			wrap.className = "flex gap-3 flex-wrap mb-4";
			sample[key].forEach((tag) => {
				wrap.innerHTML += `<div class="tag tag-${key.split("_")[0]}"><p class="tag-text">#${tag}</p></div>`;
			});
			tagsEl.appendChild(wrap);
		}
	});
}

// Init demo thumbs on DOM ready
if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", renderDemoThumbs);
} else {
	renderDemoThumbs();
}

// -------- End Demo simulation --------

// Function to navigate between steps
function goToStep(step) {
	document.querySelectorAll(".step").forEach((s) => s.classList.remove("active"));
	document.getElementById(`step${step}`).classList.add("active");
	currentStep = step;

	// Reset image data when returning to step 1
	if (step === 1) {
		currentImageData = null;
		uploadedImage = null;
	}
}

// Handle image upload
document.addEventListener("DOMContentLoaded", function () {
	// Image upload event listener
	document.getElementById("imageUpload").addEventListener("change", function (e) {
		const file = e.target.files[0];
		if (file) {
			// Reset image data for the new upload
			uploadedImage = file;
			currentImageData = null;
			const reader = new FileReader();
			reader.onload = function (e) {
				// Update the image src instead of background-image
				const previewImg = document.getElementById("previewImage");
				const analysisImg = document.getElementById("analysisImage");

				previewImg.src = e.target.result;
				analysisImg.src = e.target.result;

				// Show the images
				previewImg.style.display = "block";
				analysisImg.style.display = "block";

				goToStep(2);
			};
			reader.readAsDataURL(file);
		}
	});

	// Add button event listeners
	document.querySelectorAll("[data-action]").forEach((button) => {
		button.addEventListener("click", function () {
			const action = this.getAttribute("data-action");

			if (action === "upload") {
				document.getElementById("imageUpload").click();
			} else if (action === "goToStep") {
				const step = parseInt(this.getAttribute("data-step"));
				goToStep(step);
			} else if (action === "analyze") {
				document.getElementById("step3").classList.add("analyzing");
				goToStep(3);
				startAnalysisAnimation();
				analyzeImage();
			}
		});
	});

	// after listeners
	document.getElementById("export-csv").addEventListener("click", () => {
		const params = new URLSearchParams({ tag: lib.tag, search: lib.search, sort: lib.sort });
		window.location = "ajax/export.php?format=csv&" + params.toString();
	});
	document.getElementById("export-json").addEventListener("click", () => {
		const params = new URLSearchParams({ tag: lib.tag, search: lib.search, sort: lib.sort });
		window.location = "ajax/export.php?format=json&" + params.toString();
	});
});

// Function to upload to the server
async function uploadToServer(file) {
	const formData = new FormData();
	formData.append("image", file);

	try {
		const response = await fetch("ajax/upload.php", {
			method: "POST",
			body: formData,
		});
		return await response.json();
	} catch (error) {
		console.error("Upload error:", error);
		return { error: "Network error during upload" };
	}
}

// Function to analyze image
async function analyzeImage() {
	if (!uploadedImage) {
		alert("Please upload an image first");
		return;
	}

	// Show loading state
	document.getElementById("imageDescription").textContent = "Analyzing image";
	document.getElementById("countdown").textContent = "15";
	document.getElementById("imageTags").innerHTML = "";

	try {
		// Upload the image first if not already uploaded
		let imageData;
		if (!currentImageData) {
			const uploadResult = await uploadToServer(uploadedImage);
			if (uploadResult.error) {
				throw new Error(uploadResult.error);
			}
			imageData = uploadResult;
			currentImageData = imageData;
		} else {
			imageData = currentImageData;
		}

		// Now request analysis
		const response = await fetch("ajax/analyze.php", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify({
				image_id: imageData.image_id,
				image_url: imageData.url,
			}),
		});

		const result = await response.json();
		if (result.error) {
			throw new Error(result.error);
		}

		// Display results
		const analysis = result.analysis;
		document.getElementById("imageDescription").textContent = analysis.description;

		// Clear and populate tags container
		const tagsContainer = document.getElementById("imageTags");
		tagsContainer.innerHTML = "";

		// Process all tag types
		const tagTypes = {
			content_tags: "Content",
			style_tags: "Style",
			technical_tags: "Technical",
		};

		for (const [tagType, label] of Object.entries(tagTypes)) {
			if (analysis[tagType] && analysis[tagType].length > 0) {
				// Add section title for tag type
				tagsContainer.innerHTML += `
                    <div class="w-full mb-2">
                        <p class="section-title">${label}</p>
                    </div>
                `;

				// Add tag container
				const tagContainer = document.createElement("div");
				tagContainer.className = "flex gap-3 flex-wrap mb-4 w-full";

				// Add tags
				analysis[tagType].forEach((tag) => {
					tagContainer.innerHTML += `
                        <div class="tag tag-${tagType.split("_")[0]}">
                            <p class="tag-text">#${tag}</p>
                        </div>
                    `;
				});

				tagsContainer.appendChild(tagContainer);
			}
		}

		// Update header credits if balance provided
		if (result.balance !== undefined) {
			const creditEl = document.getElementById("header-credits");
			if (creditEl) {
				creditEl.innerHTML = `Credits: <strong>${result.balance}</strong>`;
			}
		}
	} catch (error) {
		console.error("Analysis error:", error);
		document.getElementById("imageDescription").textContent = "Error: " + error.message;
	} finally {
		document.getElementById("step3").classList.remove("analyzing");
		stopAnalysisAnimation();
	}
}

// Function to start analysis animation
function startAnalysisAnimation() {
	// Reset countdown
	countdownValue = 15;
	document.getElementById("countdown").textContent = countdownValue.toString();
	document.getElementById("countdown-container").style.display = "flex";

	// Start dot animation
	animationTimer = setInterval(() => {
		dotCount = (dotCount % 3) + 1;
		let dots = ".".repeat(dotCount);
		document.getElementById("loading-dots").textContent = dots;
	}, 500);

	// Start countdown
	countdownTimer = setInterval(() => {
		countdownValue--;
		if (countdownValue <= 0) {
			clearInterval(countdownTimer);
			countdownValue = 0;
		}
		document.getElementById("countdown").textContent = countdownValue.toString();
	}, 1000);
}

// Function to stop analysis animation
function stopAnalysisAnimation() {
	clearInterval(animationTimer);
	clearInterval(countdownTimer);
	document.getElementById("countdown-container").style.display = "none";
	document.getElementById("loading-dots").textContent = "";
}

// -------- Tab switcher --------
function initTabSwitcher() {
	const navLinks = document.querySelectorAll("#tool-nav [data-key]");
	const panels = {
		how: document.getElementById("how-section"),
		demo: document.getElementById("demo-section"),
		custom: document.getElementById("custom-section"),
		library: document.getElementById("library-section"),
	};

	function show(key) {
		Object.values(panels).forEach((p) => p && p.classList.add("hidden"));
		if (panels[key]) panels[key].classList.remove("hidden");

		navLinks.forEach((lnk) => {
			const isActive = lnk.dataset.key === key;
			lnk.classList.toggle("bg-[#FB2091]", isActive);
			lnk.classList.toggle("text-white", isActive);
			lnk.classList.toggle("hover:text-white", isActive);
			lnk.classList.toggle("hover:text-[#FB2091]", !isActive);
			lnk.classList.toggle("shadow", isActive);
			lnk.classList.toggle("bg-gray-100", !isActive);
			lnk.classList.toggle("text-gray-600", !isActive);
		});

		if (key === "library") {
			loadLibrary();
		}
	}

	const keyElems = document.querySelectorAll("[data-key]");
	keyElems.forEach((lnk) => {
		lnk.addEventListener("click", (e) => {
			e.preventDefault();
			show(lnk.dataset.key);
		});
	});

	// keyboard arrow navigation
	document.addEventListener("keydown", (e) => {
		if (!["ArrowLeft", "ArrowRight"].includes(e.key)) return;
		const keys = ["how", "demo", "custom", "library"];
		const activeIdx = keys.findIndex((k) => !panels[k].classList.contains("hidden"));
		let newIdx = activeIdx + (e.key === "ArrowRight" ? 1 : -1);
		if (newIdx < 0) newIdx = keys.length - 1;
		if (newIdx >= keys.length) newIdx = 0;
		show(keys[newIdx]);
	});

	// initial state
	show("how");

	// -------- Library logic --------
	const lib = {
		page: 1,
		tag: "",
		search: "",
		sort: "newest",
	};

	async function loadLibrary() {
		const params = new URLSearchParams({
			page: lib.page,
			tag: lib.tag,
			search: lib.search,
			sort: lib.sort,
		});
		const resp = await fetch("ajax/library_data.php?" + params.toString());
		const json = await resp.json();
		renderLibrary(json);
	}

	function renderLibrary(data) {
		const grid = document.getElementById("library-grid");
		grid.innerHTML = "";
		data.images.forEach((img) => {
			const div = document.createElement("div");
			div.innerHTML = `<img src="${img.thumb}" alt="thumb" class="w-full h-auto rounded shadow" loading="lazy" />`;
			grid.appendChild(div);
		});

		// filters
		const filterWrap = document.getElementById("library-filters");
		filterWrap.innerHTML = "";
		data.topTags.forEach((t) => {
			const btn = document.createElement("button");
			btn.className = `px-3 py-1 rounded-full text-sm ${lib.tag === t ? "bg-[#FB2091] text-white" : "bg-gray-100 text-gray-700"}`;
			btn.textContent = `#${t}`;
			btn.addEventListener("click", () => {
				lib.tag = lib.tag === t ? "" : t;
				lib.page = 1;
				loadLibrary();
			});
			filterWrap.appendChild(btn);
		});

		// pagination
		const pag = document.getElementById("library-pagination");
		pag.innerHTML = `Page ${data.page} / ${data.totalPages}`;
		// next prev
		const prev = document.createElement("button");
		prev.textContent = "← Prev";
		prev.disabled = data.page <= 1;
		const next = document.createElement("button");
		next.textContent = "Next →";
		next.disabled = data.page >= data.totalPages;
		prev.className = "px-2 py-1 rounded border text-sm";
		next.className = "px-2 py-1 rounded border text-sm";
		prev.addEventListener("click", () => {
			lib.page--;
			loadLibrary();
		});
		next.addEventListener("click", () => {
			lib.page++;
			loadLibrary();
		});
		pag.prepend(prev);
		pag.appendChild(next);
	}

	// listeners
	document.getElementById("library-sort").addEventListener("change", (e) => {
		lib.sort = e.target.value;
		lib.page = 1;
		loadLibrary();
	});
	document.getElementById("library-search").addEventListener("input", (e) => {
		lib.search = e.target.value.trim();
		lib.page = 1;
		loadLibrary();
	});

	// export buttons listeners now inside init so have access to lib
	document.getElementById("export-csv").addEventListener("click", () => {
		const params = new URLSearchParams({ tag: lib.tag, search: lib.search, sort: lib.sort });
		window.location = "ajax/export.php?format=csv&" + params.toString();
	});
	document.getElementById("export-json").addEventListener("click", () => {
		const params = new URLSearchParams({ tag: lib.tag, search: lib.search, sort: lib.sort });
		window.location = "ajax/export.php?format=json&" + params.toString();
	});
}

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", initTabSwitcher);
} else {
	initTabSwitcher();
}
// -------- End Tab switcher --------
