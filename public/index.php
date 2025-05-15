<?php
// Shared infrastructure bootstrapping for image-analyzer
$projectRoot = __DIR__ . '/../';
require_once $projectRoot . 'require_tools.php';

use Sk00p\UI;
use Sk00p\User;
use Sk00p\Config;

$user = User::current();
$baseUrl = Config::env('BASE_URL', 'https://sk00p.com');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>sk00p Tag Genius</title>
    <?php UI::favicon(); ?>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link rel="stylesheet" as="style" onload="this.rel='stylesheet'"
        href="https://fonts.googleapis.com/css2?display=swap&amp;family=Manrope:wght@400;500;700;800&amp;family=Noto+Sans:wght@400;500;700;900" />
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>

<body class="bg-white">
    <?php UI::header(); ?>

    <!-- Hero band -->
    <section class="text-center py-12 px-6 bg-gradient-to-b from-white to-gray-50">
        <h1 class="text-3xl md:text-4xl font-extrabold mb-4">Tag Genius</h1>
        <p class="text-gray-600 max-w-xl mx-auto mb-6">Turn any image into rich, searchable metadata in seconds.</p>
    </section>

    <!-- Tab navigation -->
    <nav id="tool-nav" class="max-w-4xl mx-auto flex justify-center gap-3 text-sm font-medium mt-4 mb-8" role="tablist">
        <a href="#how-section" data-key="how"
            class="nav-link py-2 px-4 rounded-full bg-gray-100 text-gray-600 hover:text-[#FB2091] focus:outline-none focus:ring-2 focus:ring-pink-500">1&nbsp;·&nbsp;How
            it works</a>
        <a href="#demo-section" data-key="demo"
            class="nav-link py-2 px-4 rounded-full bg-gray-100 text-gray-600 hover:text-[#FB2091] focus:outline-none focus:ring-2 focus:ring-pink-500">2&nbsp;·&nbsp;Free
            demo</a>
        <a href="#custom-section" data-key="custom"
            class="nav-link py-2 px-4 rounded-full bg-gray-100 text-gray-600 hover:text-[#FB2091] focus:outline-none focus:ring-2 focus:ring-pink-500">3&nbsp;·&nbsp;Your
            images</a>
        <a href="#library-section" data-key="library"
            class="nav-link py-2 px-4 rounded-full bg-gray-100 text-gray-600 hover:text-[#FB2091] focus:outline-none focus:ring-2 focus:ring-pink-500">4&nbsp;·&nbsp;Your
            Tag Library</a>
    </nav>

    <div class="relative flex size-full min-h-screen flex-col bg-white overflow-x-hidden"
        style="font-family: Manrope, 'Noto Sans', sans-serif">

        <!-- Example Section -->
        <section id="how-section" class="w-full px-6 py-10 bg-white border rounded-xl shadow-md max-w-4xl mx-auto">
            <div class="max-w-4xl mx-auto text-center mb-6">
                <h2 class="text-2xl font-bold mb-3">How Tag Genius works</h2>
                <p class="text-gray-600">Below is an example of Tag Genius in action. We analyzed a sample image and
                    generated descriptive tags.</p>
            </div>
            <div class="max-w-4xl mx-auto flex flex-col md:flex-row gap-6 items-start justify-center">
                <img src="https://sc00p-v01.s3.amazonaws.com/sample-images/cat-on-sofa.webp" alt="Sample"
                    class="w-full md:w-1/2 rounded-lg shadow" />
                <div class="flex-1 grid grid-cols-2 gap-4" id="example-tags">
                    <div class="col-span-2 text-sm text-gray-500">Content tags</div>
                    <span class="px-3 py-1 rounded-full bg-pink-50 text-pink-600 text-sm">#cat</span>
                    <span class="px-3 py-1 rounded-full bg-pink-50 text-pink-600 text-sm">#sofa</span>

                    <div class="col-span-2 mt-4 text-sm text-gray-500">Style tags</div>
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-sm">#bright lighting</span>
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-sm">#close-up</span>

                    <div class="col-span-2 mt-4 text-sm text-gray-500">Technical tags</div>
                    <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-sm">#photography</span>
                    <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-sm">#depth of field</span>
                </div>
            </div>
            <div class="max-w-4xl mx-auto text-center mt-8">
                <a href="#demo-section" data-key="demo"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-[#FB2091] text-white text-sm font-semibold shadow hover:bg-pink-600 transition">
                    Try a free demo
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12" />
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </section>

        <!-- Interactive Demo Section (no credits) -->
        <section id="demo-section"
            class="w-full px-6 py-10 bg-white border rounded-xl shadow-md max-w-4xl mx-auto hidden">
            <div class="max-w-4xl mx-auto text-center mb-6">
                <h2 class="text-2xl font-bold mb-3">Try a quick demo &mdash; no login required</h2>
                <p class="text-gray-600">Tap any sample image to see how the analyzer works &mdash; no credits used, no
                    login required.</p>
            </div>
            <div class="max-w-4xl mx-auto grid grid-cols-[repeat(auto-fit,minmax(120px,1fr))] gap-4 mb-8"
                id="demo-thumbs">
                <!-- Thumbnails inserted by JS -->
            </div>
            <div class="max-w-4xl mx-auto hidden" id="demo-results">
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    <img id="demo-image" src="" alt="Demo" class="w-full md:w-1/2 rounded-lg shadow" />
                    <div class="flex-1" id="demo-tags"></div>
                </div>
            </div>
            <div class="max-w-4xl mx-auto text-center mt-8">
                <a href="#custom-section" data-key="custom"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-[#FB2091] text-white text-sm font-semibold shadow hover:bg-pink-600 transition">
                    Try your own image
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12" />
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </section>

        <!-- Real tool heading -->
        <section id="custom-section"
            class="w-full px-6 py-10 bg-white border rounded-xl shadow-md max-w-4xl mx-auto hidden">
            <div class="max-w-4xl mx-auto text-center mb-6">
                <h2 class="text-2xl font-bold mb-3">Analyze your own image</h2>
                <p class="text-gray-600">Upload any JPG/PNG up to 10&nbsp;MB and get AI-generated tags.&nbsp;<span
                        class="font-semibold text-pink-600">Cost: 1 credit</span></p>
            </div>
            <?php if (!$user): ?>
                <div class="text-center py-10 text-gray-600">
                    <p class="mb-2">Log in or create a free account to analyze your images.<br><span
                            class="font-semibold text-pink-600">New members receive 15 free credits!</span></p>
                    <div class="flex justify-center gap-4">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/login.php?continue=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                            class="px-5 py-2 rounded-xl bg-[#FB2091] text-white font-semibold">Log&nbsp;in</a>
                        <a href="<?= htmlspecialchars($baseUrl) ?>/login.php?continue=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                            class="px-5 py-2 rounded-xl border-2 border-[#FB2091] text-[#FB2091] font-semibold bg-white">Join&nbsp;Free</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Wizard container -->
                <div class="wizard space-y-6">
                    <div class="content-container">
                        <!-- Step 1: Upload Image -->
                        <div id="step1" class="step active">
                            <div class="flex flex-wrap justify-between gap-3 p-4">
                                <div class="w-full text-center mb-2">
                                    <h3 class="text-lg font-semibold">Step&nbsp;1 &mdash; Upload an image</h3>
                                    <p class="text-sm text-gray-500">JPG or PNG, up to 10&nbsp;MB</p>
                                </div>
                            </div>
                            <div class="flex px-4 py-3 justify-center">
                                <input type="file" id="imageUpload" accept="image/jpeg,image/png" class="hidden" />
                                <button data-action="upload" class="button button-primary">
                                    <span class="truncate">Upload Photo</span>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Review -->
                        <div id="step2" class="step">
                            <div class="flex flex-wrap justify-between gap-3 p-4">
                                <div class="w-full text-center mb-2">
                                    <h3 class="text-lg font-semibold">Step&nbsp;2 &mdash; Review your image</h3>
                                    <p class="text-sm text-gray-500">Make sure it looks correct before continuing</p>
                                </div>
                            </div>
                            <div class="flex w-full grow bg-white @container p-4">
                                <div class="image-container">
                                    <img id="previewImage" class="image-preview" src="" alt="Image preview"
                                        style="display: none" />
                                </div>
                            </div>
                            <div class="flex px-4 py-3 justify-center gap-4">
                                <button data-action="goToStep" data-step="1" class="button button-secondary">
                                    <span class="truncate">Upload Different Image</span>
                                </button>
                                <div class="flex flex-col items-center gap-1">
                                    <button data-action="analyze" class="button button-primary">
                                        <span class="truncate">Analyze This Image</span>
                                    </button>
                                    <span class="text-xs text-gray-500">Cost: 1 credit</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Image Description -->
                        <div id="step3" class="step">
                            <div class="flex flex-wrap justify-between gap-3 p-4">
                                <div class="w-full text-center mb-2">
                                    <h3 class="text-lg font-semibold">Step&nbsp;3 &mdash; Image analysis</h3>
                                </div>
                            </div>
                            <div class="flex w-full grow bg-white @container p-4">
                                <div class="image-container">
                                    <img id="analysisImage" class="image-preview" src="" alt="Analyzed image"
                                        style="display: none" />
                                </div>
                            </div>
                            <div class="p-4">
                                <p class="section-title">Description</p>
                                <div class="flex items-center">
                                    <p id="imageDescription" class="description-text">Loading description</p>
                                    <span id="loading-dots" class="description-text"></span>
                                </div>
                                <div id="countdown-container" class="mt-2 text-sm text-gray-500 flex items-center"
                                    style="display: none">
                                    <span>Estimated time remaining: </span>
                                    <span id="countdown" class="font-bold ml-1">15</span>
                                    <span class="ml-1">seconds</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <p class="section-title">Tags</p>
                                <div id="imageTags" class="flex flex-col gap-3"></div>
                            </div>
                            <div class="flex px-4 py-3 justify-center gap-4">
                                <button data-action="goToStep" data-step="1" class="button button-primary">
                                    <span class="truncate">Analyze Another Image</span>
                                </button>
                                <a href="#library-section" data-key="library"
                                    class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-[#FB2091] text-white text-sm font-semibold shadow hover:bg-pink-600 transition">
                                    <span>View your tag library</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                        <polyline points="12 5 19 12 12 19" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Tag Library Section -->
        <section id="library-section"
            class="w-full px-6 py-10 bg-white border rounded-xl shadow-md max-w-4xl mx-auto hidden">
            <div class="flex flex-col gap-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-2xl font-bold">Your tag library</h2>
                    <div class="flex gap-2">
                        <button id="export-csv" class="button button-secondary text-sm">Export CSV</button>
                        <button id="export-json" class="button button-secondary text-sm">Export JSON</button>
                    </div>
                </div>

                <?php if (!$user): ?>
                    <div class="text-center py-10 text-gray-600 w-full space-y-4">
                        <p class="mb-2">Log in or create a free account to save analyses and build your tag
                            library.<br><span class="font-semibold text-pink-600">New members receive 15 free
                                credits!</span></p>
                        <div class="flex justify-center gap-4">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/login.php?continue=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                                class="px-5 py-2 rounded-xl bg-[#FB2091] text-white font-semibold">Log&nbsp;in</a>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/login.php?continue=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                                class="px-5 py-2 rounded-xl border-2 border-[#FB2091] text-[#FB2091] font-semibold bg-white">Join&nbsp;Free</a>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600 text-sm max-w-2xl">Browse every image you've analyzed so far. Use the tag pills
                        or search box to filter. Export the current view as CSV/JSON to reuse metadata in your ad manager or
                        any workflow.</p>

                    <!-- Tag filters injected here -->
                    <div id="library-filters" class="flex flex-wrap gap-2"></div>

                    <!-- Search & sort -->
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <input id="library-search" type="text" placeholder="Search tags…"
                            class="border rounded px-3 py-1 text-sm" />
                        <select id="library-sort" class="border rounded px-2 py-1 text-sm">
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                            <option value="filename">Filename</option>
                            <option value="tagcount">Most tags</option>
                        </select>
                    </div>

                    <!-- Grid -->
                    <div id="library-grid" class="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-3"></div>

                    <!-- Empty state -->
                    <div id="library-empty" class="flex flex-col items-center gap-4 py-10 text-gray-500 hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 16.5V8a5 5 0 015-5h8a5 5 0 015 5v8.5M3 16.5l3 3m0 0l3-3m-3 3V13m12 3.5l3 3m0 0l3-3m-3 3V13" />
                        </svg>
                        <p class="text-center max-w-xs">No analyzed images yet. Run an analysis in Step&nbsp;3 and return
                            here to build your library.</p>
                    </div>

                    <!-- Pagination -->
                    <div id="library-pagination" class="flex justify-between items-center text-sm mt-4"></div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <script src="js/app.js"></script>
</body>

</html>