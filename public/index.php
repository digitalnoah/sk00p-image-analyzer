<?php
// Shared infrastructure bootstrapping for image-analyzer
$projectRoot = __DIR__ . '/../';
require_once $projectRoot . 'require_tools.php';

use Sk00p\UI;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>sk00p Image Analyzer</title>
    <?php UI::favicon(); ?>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link rel="stylesheet" as="style" onload="this.rel='stylesheet'"
        href="https://fonts.googleapis.com/css2?display=swap&amp;family=Manrope:wght@400;500;700;800&amp;family=Noto+Sans:wght@400;500;700;900" />
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>

<body class="bg-white">
    <?php UI::header(); ?>
    <div class="relative flex size-full min-h-screen flex-col bg-white overflow-x-hidden"
        style="font-family: Manrope, 'Noto Sans', sans-serif">
        <div class="layout-container flex h-full grow flex-col">
            <div class="px-40 flex flex-1 justify-center py-5">
                <div class="content-container">
                    <!-- Step 1: Upload Image -->
                    <div id="step1" class="step active">
                        <div class="flex flex-wrap justify-between gap-3 p-4">
                            <div class="flex min-w-72 flex-col gap-3">
                                <p class="title">Upload an Image</p>
                                <p class="subtitle">Supported formats: JPG, PNG. Max size 10MB</p>
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
                            <div class="flex min-w-72 flex-col gap-3">
                                <p class="title">Review Your Image</p>
                                <p class="subtitle">Preview your image before analysis</p>
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
                            <div class="flex min-w-72 flex-col gap-3">
                                <p class="title">Image Analysis</p>
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
                        <div class="flex px-4 py-3 justify-center">
                            <button data-action="goToStep" data-step="1" class="button button-primary">
                                <span class="truncate">Analyze Another Image</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/app.js"></script>
</body>

</html>