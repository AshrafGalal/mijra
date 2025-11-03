const fs = require('fs').promises;
const path = require('path');
const sharp = require('sharp');
const crypto = require('crypto');

/**
 * Save media (image or file) from base64, compress if image, and generate thumbnail.
 * @param {Object} media - { data: base64, mimetype, filename }
 * @param {String} tenantId
 * @returns {Object|null} media info { local_path, mimetype, size, name, thumbnail_path }
 */
async function saveMedia(media, tenantId) {
    try {
        if (!media || !media.data) return null;

        const buffer = Buffer.from(media.data, 'base64');
        const uploadDir = path.join('/var/www/html/storage/app/temp', tenantId);
        await fs.mkdir(uploadDir, { recursive: true });

        const mime = media.mimetype || 'application/octet-stream';
        const ext =
            media.filename
                ? path.extname(media.filename).slice(1)
                : mime.split('/')[1]?.split(';')[0] || 'bin';

        const hash = crypto.randomBytes(8).toString('hex');
        const filename = `${Date.now()}-${hash}.${ext}`;
        const filePath = path.join(uploadDir, filename);

        let thumbnailPath = null;

        if (mime.startsWith('image/')) {
            // Save compressed image
            await compressAndSaveImage(buffer, filePath, mime);
        } else {
            // Save raw file (non-image)
            await fs.writeFile(filePath, buffer);
        }

        const stats = await fs.stat(filePath);

        return {
            local_path: `storage/app/temp/${tenantId}/${filename}`,
            mimetype: mime,
            size: stats.size,
            name: media.filename || filename,
        };
    } catch (err) {
        console.error(`❌ Error saving media for tenant ${tenantId}:`, err.message);
        return null;
    }
}

/**
 * Compress and save image based on type
 * @param {Buffer} buffer
 * @param {String} outputPath
 * @param {String} mime
 */
async function compressAndSaveImage(buffer, outputPath, mime) {
    const image = sharp(buffer);
    const metadata = await image.metadata();
    const width = metadata.width || 1280;

    let pipeline = image.resize({
        width: width > 1280 ? 1280 : width,
        withoutEnlargement: true,
    });

    if (mime === 'image/png') {
        pipeline = pipeline.png({
            compressionLevel: 9,
            adaptiveFiltering: true,
            palette: true,
        });
    } else if (mime === 'image/webp') {
        pipeline = pipeline.webp({ quality: 65 });
    } else {
        pipeline = pipeline.jpeg({ quality: 65, progressive: true, mozjpeg: true });
    }

    await pipeline.toFile(outputPath);
}

module.exports = {
    saveMedia,
};
