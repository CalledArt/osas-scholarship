---
paths:
  - 'tests/**'
---

# Tests

## Fake images without GD
UploadedFile::fake()->image() needs the GD extension, which local PHP lacks; two tests errored on it until 2026-10-27. Fake pictures the way the rest of the suite does: UploadedFile::fake()->create('picture.jpg', 100, 'image/jpeg'). The mimes rule reads the given type, and the apply flow never decodes the image (only BuildApplicationsReport does, through PrintedImage, which checks for GD itself).
