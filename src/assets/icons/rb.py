#!/usr/bin/env python3
"""
remove_backgrounds.py
Quita el fondo a todas las imágenes en la carpeta actual.

Uso:
  python remove_backgrounds.py [--output-dir OUT] [--method auto|rembg|grabcut] [--force] [--inplace]

Salida: archivos PNG con fondo transparente: <nombre>_no_bg.png (o sobrescribe si --inplace).
"""
from pathlib import Path
import argparse
from PIL import Image
import io
import sys

# backends try
try:
    from rembg import remove as rembg_remove
    HAVE_REMBG = True
except Exception:
    HAVE_REMBG = False

try:
    import cv2
    import numpy as np
    HAVE_CV2 = True
except Exception:
    HAVE_CV2 = False

SUPPORTED = ('.jpg', '.jpeg', '.png', '.webp', '.tif', '.tiff', '.bmp')

def remove_with_rembg(path: Path) -> Image.Image:
    with open(path, 'rb') as f:
        inp = f.read()
    out_bytes = rembg_remove(inp)
    return Image.open(io.BytesIO(out_bytes)).convert('RGBA')

def remove_with_grabcut(path: Path) -> Image.Image:
    img = cv2.imread(str(path), cv2.IMREAD_COLOR)
    if img is None:
        raise RuntimeError("No se pudo leer la imagen")
    h, w = img.shape[:2]
    rect = (int(w*0.05), int(h*0.05), int(w*0.9), int(h*0.9))
    mask = np.zeros((h, w), np.uint8)
    bgd = np.zeros((1,65), np.float64)
    fgd = np.zeros((1,65), np.float64)
    cv2.grabCut(img, mask, rect, bgd, fgd, 5, cv2.GC_INIT_WITH_RECT)
    mask2 = np.where((mask==2)|(mask==0), 0, 1).astype('uint8')
    rgba = cv2.cvtColor(img, cv2.COLOR_BGR2RGBA)
    rgba[:, :, 3] = (mask2 * 255).astype('uint8')
    return Image.fromarray(rgba)

def process_file(path: Path, outdir: Path, method: str, force: bool, inplace: bool):
    outname = (path.stem + "_no_bg.png") if not inplace else (path.with_suffix('.png').name)
    outpath = outdir / outname
    if outpath.exists() and not force:
        print(f"Skip {path.name}: {outpath.name} exists (use --force to overwrite)")
        return
    try:
        if method == 'rembg':
            if not HAVE_REMBG:
                raise RuntimeError("rembg no está instalado")
            out = remove_with_rembg(path)
        elif method == 'grabcut':
            if not HAVE_CV2:
                raise RuntimeError("opencv-python no está instalado")
            out = remove_with_grabcut(path)
        else:  # auto
            if HAVE_REMBG:
                out = remove_with_rembg(path)
            elif HAVE_CV2:
                out = remove_with_grabcut(path)
            else:
                raise RuntimeError("No hay backend disponible (instala 'rembg' o 'opencv-python')")
        out.save(outpath)
        print(f"Saved: {outpath}")
    except Exception as e:
        print(f"Error procesando {path.name}: {e}")

def main():
    p = argparse.ArgumentParser(description="Quita fondo a imágenes en la carpeta actual")
    p.add_argument('--output-dir', '-o', type=Path, default=Path('output'), help='Carpeta de salida')
    p.add_argument('--method', choices=['auto','rembg','grabcut'], default='auto', help='Backend a usar')
    p.add_argument('--force', action='store_true', help='Sobrescribir salidas existentes')
    p.add_argument('--inplace', action='store_true', help='Guardar sobre la imagen original (con extensión .png)')
    args = p.parse_args()

    if args.inplace:
        outdir = Path('.')
    else:
        outdir = args.output_dir
        outdir.mkdir(parents=True, exist_ok=True)

    files = [x for x in Path('.').iterdir() if x.is_file() and x.suffix.lower() in SUPPORTED]
    if not files:
        print("No se encontraron imágenes en la carpeta actual.")
        return

    print("Backends disponibles:", "rembg" if HAVE_REMBG else "-", "opencv" if HAVE_CV2 else "-")
    for f in files:
        process_file(f, outdir, args.method, args.force, args.inplace)

if __name__ == '__main__':
    main()