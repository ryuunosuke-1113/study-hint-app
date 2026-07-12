function initializeStudyHints() {
    initializeBookFilter();
    initializeHintToggles();
    initializeLightbox();
    initializeImagePasteAreas();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeStudyHints);
} else {
    initializeStudyHints();
}
/**
 * 科目に応じて参考書を絞り込みます。
 *
 * 登録画面・編集画面・検索画面の
 * subject_id / book_idで共通利用します。
 */
function initializeBookFilter() {
    const subjectSelect = document.getElementById("subject_id");
    const bookSelect = document.getElementById("book_id");

    if (!subjectSelect || !bookSelect) {
        return;
    }

    /*
     * option要素は、絞り込みで取り外す前に
     * 複製して保存しておきます。
     */
    const allBookOptions = Array.from(bookSelect.options).map((option) =>
        option.cloneNode(true),
    );

    function filterBooks(resetBook = false) {
        const selectedSubjectId = subjectSelect.value;
        const currentBookId = bookSelect.value;

        bookSelect.innerHTML = "";

        allBookOptions.forEach((option) => {
            const optionSubjectId = option.dataset.subjectId ?? "";

            const shouldDisplay =
                option.value === "" ||
                selectedSubjectId === "" ||
                optionSubjectId === selectedSubjectId;

            if (shouldDisplay) {
                bookSelect.appendChild(option.cloneNode(true));
            }
        });

        if (resetBook) {
            bookSelect.value = "";
            return;
        }

        /*
         * 編集画面や検索後では、
         * 現在選択されている参考書を維持します。
         */
        const currentOptionExists = Array.from(bookSelect.options).some(
            (option) => option.value === currentBookId,
        );

        bookSelect.value = currentOptionExists ? currentBookId : "";
    }

    filterBooks(false);

    subjectSelect.addEventListener("change", () => {
        filterBooks(true);
    });
}

/**
 * 一覧画面でヒント本文を開閉します。
 */
function initializeHintToggles() {
    const toggleButtons = document.querySelectorAll(".hint-toggle-button");

    toggleButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const targetId = button.dataset.target;
            const hintNumber = button.dataset.hintNumber;

            if (!targetId) {
                return;
            }

            const target = document.getElementById(targetId);

            if (!target) {
                return;
            }

            const willOpen = target.hidden;

            target.hidden = !willOpen;

            button.setAttribute("aria-expanded", willOpen ? "true" : "false");

            button.textContent = willOpen
                ? `ヒント${hintNumber}を隠す`
                : `ヒント${hintNumber}を表示`;
        });
    });
}

/**
 * 一覧画面・編集画面の画像を
 * ライトボックスで拡大表示します。
 */
function initializeLightbox() {
    const lightbox = document.getElementById("image-lightbox");

    const lightboxImage = document.getElementById("lightbox-image");

    const closeButton = document.getElementById("lightbox-close");

    if (!lightbox || !lightboxImage || !closeButton) {
        return;
    }

    function openLightbox(imageUrl, altText = "拡大表示") {
        if (!imageUrl) {
            return;
        }

        lightboxImage.src = imageUrl;
        lightboxImage.alt = altText;

        lightbox.classList.add("is-open");
        lightbox.setAttribute("aria-hidden", "false");

        document.body.style.overflow = "hidden";

        closeButton.focus();
    }

    function closeLightbox() {
        lightbox.classList.remove("is-open");
        lightbox.setAttribute("aria-hidden", "true");

        lightboxImage.src = "";
        lightboxImage.alt = "拡大表示";

        document.body.style.overflow = "";
    }

    /*
     * イベント委譲を使うことで、
     * 後から追加された画像にも対応できます。
     */
    document.addEventListener("click", (event) => {
        const thumbnail = event.target.closest(".hint-thumbnail");

        if (!thumbnail) {
            return;
        }

        const imageUrl =
            thumbnail.dataset.fullImage || thumbnail.getAttribute("src");

        openLightbox(imageUrl, thumbnail.getAttribute("alt") || "拡大表示");
    });

    closeButton.addEventListener("click", closeLightbox);

    lightbox.addEventListener("click", (event) => {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && lightbox.classList.contains("is-open")) {
            closeLightbox();
        }
    });
}

/**
 * ヒント1〜3の画像貼り付け欄を初期化します。
 */
function initializeImagePasteAreas() {
    for (let index = 0; index < 3; index += 1) {
        initializeImagePasteArea(index);
    }
}

/**
 * 指定されたヒント番号の画像貼り付け処理です。
 */
/**
 * 指定されたヒント番号の画像選択・貼り付け処理です。
 */
function initializeImagePasteArea(index) {
    const pasteArea = document.getElementById(`image-paste-area-${index}`);

    const fileInput = document.getElementById(`hint-image-${index}`);

    const status = document.getElementById(`paste-status-${index}`);

    const colorCheckbox = document.getElementById("save-images-in-color");

    if (!pasteArea || !fileInput) {
        return;
    }

    /*
     * スクリーンショット貼り付け
     */
    pasteArea.addEventListener("paste", async (event) => {
        const clipboardItems = event.clipboardData?.items;

        if (!clipboardItems) {
            showPasteStatus(
                status,
                "貼り付けられたデータを取得できませんでした。",
                true,
            );

            return;
        }

        const imageItem = Array.from(clipboardItems).find((item) => {
            return item.type.startsWith("image/");
        });

        if (!imageItem) {
            showPasteStatus(
                status,
                "画像が見つかりませんでした。スクリーンショットをコピーしてから貼り付けてください。",
                true,
            );

            return;
        }

        const originalFile = imageItem.getAsFile();

        if (!originalFile) {
            showPasteStatus(
                status,
                "画像ファイルを取得できませんでした。",
                true,
            );

            return;
        }

        event.preventDefault();

        const saveInColor = colorCheckbox?.checked ?? false;

        showPasteStatus(
            status,
            saveInColor
                ? "カラー画像を処理しています…"
                : "画像をモノクロに変換しています…",
            false,
        );

        try {
            const processedFile = await processImageFile(
                originalFile,
                index,
                saveInColor,
            );

            const wasSet = setFileInput(fileInput, processedFile);

            if (!wasSet) {
                throw new Error("画像をファイル欄へ設定できませんでした。");
            }

            clearPasteArea(pasteArea);

            showPasteStatus(
                status,
                saveInColor
                    ? `カラー画像を貼り付けました：${processedFile.name}`
                    : `モノクロ画像を貼り付けました：${processedFile.name}`,
                false,
            );
        } catch (error) {
            console.error("貼り付け画像の処理に失敗しました。", error);

            showPasteStatus(
                status,
                "画像を処理できませんでした。別の画像を選択するか、もう一度お試しください。",
                true,
            );
        }
    });

    /*
     * 通常のファイル選択
     */
    fileInput.addEventListener("change", async () => {
        if (fileInput.dataset.processing === "true") {
            return;
        }

        const originalFile = fileInput.files?.[0];

        if (!originalFile) {
            showPasteStatus(status, "", false);

            return;
        }

        if (!originalFile.type.startsWith("image/")) {
            fileInput.value = "";

            showPasteStatus(status, "画像ファイルを選択してください。", true);

            return;
        }

        const saveInColor = colorCheckbox?.checked ?? false;

        showPasteStatus(
            status,
            saveInColor
                ? "カラー画像を処理しています…"
                : "画像をモノクロに変換しています…",
            false,
        );

        try {
            const processedFile = await processImageFile(
                originalFile,
                index,
                saveInColor,
            );

            fileInput.dataset.processing = "true";

            const wasSet = setFileInput(fileInput, processedFile);

            delete fileInput.dataset.processing;

            if (!wasSet) {
                throw new Error(
                    "処理後の画像をファイル欄へ設定できませんでした。",
                );
            }

            showPasteStatus(
                status,
                saveInColor
                    ? `カラー画像を選択しました：${processedFile.name}`
                    : `モノクロ画像を選択しました：${processedFile.name}`,
                false,
            );
        } catch (error) {
            delete fileInput.dataset.processing;

            console.error("選択画像の処理に失敗しました。", error);

            fileInput.value = "";

            showPasteStatus(
                status,
                "画像を処理できませんでした。別の画像を選択してください。",
                true,
            );
        }
    });

    /*
     * contenteditable内に文字や画像が残らないようにします。
     */
    pasteArea.addEventListener("input", () => {
        clearPasteArea(pasteArea);
    });
}

/**
 * 選択・貼り付けされた画像をリサイズし、
 * カラーまたはモノクロのJPEGへ変換します。
 */
async function processImageFile(originalFile, index, saveInColor) {
    const image = await loadImageFromFile(originalFile);

    const maxDimension = 2000;

    const { width, height } = calculateImageSize(
        image.naturalWidth,
        image.naturalHeight,
        maxDimension,
    );

    const canvas = document.createElement("canvas");

    canvas.width = width;
    canvas.height = height;

    const context = canvas.getContext("2d", {
        willReadFrequently: !saveInColor,
    });

    if (!context) {
        throw new Error("Canvasを利用できませんでした。");
    }

    /*
     * PNGなどの透明部分が黒くならないように、
     * 背景を白で塗ります。
     */
    context.fillStyle = "#ffffff";
    context.fillRect(0, 0, width, height);

    context.drawImage(image, 0, 0, width, height);

    if (!saveInColor) {
        convertCanvasToGrayscale(context, width, height);
    }

    const blob = await canvasToBlob(canvas, "image/jpeg", 0.8);

    URL.revokeObjectURL(image.src);

    const timestamp = new Date().toISOString().replace(/[:.]/g, "-");

    const modeName = saveInColor ? "color" : "grayscale";

    const fileName = `hint-${index + 1}-${modeName}-${timestamp}.jpg`;

    return new File([blob], fileName, {
        type: "image/jpeg",
        lastModified: Date.now(),
    });
}

/**
 * Fileをブラウザで扱えるImageへ変換します。
 */
function loadImageFromFile(file) {
    return new Promise((resolve, reject) => {
        const imageUrl = URL.createObjectURL(file);

        const image = new Image();

        image.onload = () => {
            resolve(image);
        };

        image.onerror = () => {
            URL.revokeObjectURL(imageUrl);

            reject(new Error("画像を読み込めませんでした。"));
        };

        image.src = imageUrl;
    });
}

/**
 * 縦横比を保ったまま、長辺を指定サイズ以内にします。
 */
function calculateImageSize(originalWidth, originalHeight, maxDimension) {
    if (originalWidth <= maxDimension && originalHeight <= maxDimension) {
        return {
            width: originalWidth,
            height: originalHeight,
        };
    }

    const scale = Math.min(
        maxDimension / originalWidth,
        maxDimension / originalHeight,
    );

    return {
        width: Math.round(originalWidth * scale),

        height: Math.round(originalHeight * scale),
    };
}

/**
 * Canvas内の画像をグレースケール化します。
 */
function convertCanvasToGrayscale(context, width, height) {
    const imageData = context.getImageData(0, 0, width, height);

    const pixels = imageData.data;

    for (let index = 0; index < pixels.length; index += 4) {
        const red = pixels[index];
        const green = pixels[index + 1];
        const blue = pixels[index + 2];

        /*
         * 人間の目の明るさの感じ方に合わせた
         * 加重平均です。
         */
        const gray = Math.round(red * 0.299 + green * 0.587 + blue * 0.114);

        pixels[index] = gray;
        pixels[index + 1] = gray;
        pixels[index + 2] = gray;
    }

    context.putImageData(imageData, 0, 0);
}

/**
 * CanvasをBlobへ変換します。
 */
function canvasToBlob(canvas, mimeType, quality) {
    return new Promise((resolve, reject) => {
        canvas.toBlob(
            (blob) => {
                if (!blob) {
                    reject(new Error("画像ファイルを作成できませんでした。"));

                    return;
                }

                resolve(blob);
            },
            mimeType,
            quality,
        );
    });
}
/**
 * 貼り付けた画像に分かりやすいファイル名を付けます。
 */
function createNamedImageFile(originalFile, index) {
    const mimeType = originalFile.type || "image/png";

    const extension = getImageExtension(mimeType);

    const timestamp = new Date().toISOString().replace(/[:.]/g, "-");

    const fileName = `hint-${index + 1}-${timestamp}.${extension}`;

    return new File([originalFile], fileName, {
        type: mimeType,
        lastModified: Date.now(),
    });
}

/**
 * MIMEタイプから拡張子を取得します。
 */
function getImageExtension(mimeType) {
    const extensionMap = {
        "image/jpeg": "jpg",
        "image/png": "png",
        "image/gif": "gif",
        "image/webp": "webp",
        "image/heic": "heic",
        "image/heif": "heif",
    };

    return extensionMap[mimeType] || "png";
}

/**
 * DataTransferを使用して、
 * 処理後のFileをinput[type=file]へ設定します。
 */
function setFileInput(fileInput, file) {
    try {
        const dataTransfer = new DataTransfer();

        dataTransfer.items.add(file);

        fileInput.files = dataTransfer.files;

        return fileInput.files.length > 0;
    } catch (error) {
        console.error("画像をファイル入力へ設定できませんでした。", error);

        return false;
    }
}
/**
 * 貼り付け欄を初期表示へ戻します。
 */
function clearPasteArea(pasteArea) {
    pasteArea.textContent =
        "ここをタップまたはクリックして、スクリーンショットを貼り付けてください";
}

/**
 * 貼り付け・ファイル選択の結果を表示します。
 */
function showPasteStatus(statusElement, message, isError) {
    if (!statusElement) {
        return;
    }

    if (!message) {
        statusElement.textContent = "";
        statusElement.hidden = true;
        statusElement.classList.remove("is-success", "is-error");

        return;
    }

    statusElement.textContent = message;
    statusElement.hidden = false;

    statusElement.classList.toggle("is-error", isError);

    statusElement.classList.toggle("is-success", !isError);
}
