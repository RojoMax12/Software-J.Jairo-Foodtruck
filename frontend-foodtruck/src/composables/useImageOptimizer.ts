/**
 * Composable para convertir y optimizar cualquier imagen (JPG, PNG, etc.)
 * a formato WEBP ultraligero antes de enviarlo al servidor.
 */

export interface OptimizeOptions {
  maxWidth?: number;
  maxHeight?: number;
  quality?: number; // 0.1 a 1.0 (default: 0.85)
}

export function useImageOptimizer() {
  /**
   * Convierte un objeto File o Blob a un archivo File en formato .webp optimizado
   */
  const convertToWebP = (
    file: File | Blob,
    filename: string = 'imagen_producto.webp',
    options: OptimizeOptions = {}
  ): Promise<File> => {
    const { maxWidth = 1000, maxHeight = 1000, quality = 0.85 } = options;

    return new Promise((resolve, reject) => {
      const reader = new FileReader();

      reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
          let width = img.width;
          let height = img.height;

          // Redimensionar proporcionalmente si supera los límites máximos
          if (width > maxWidth || height > maxHeight) {
            if (width > height) {
              height = Math.round((height * maxWidth) / width);
              width = maxWidth;
            } else {
              width = Math.round((width * maxHeight) / height);
              height = maxHeight;
            }
          }

          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;

          const ctx = canvas.getContext('2d');
          if (!ctx) {
            return reject(new Error('No se pudo inicializar el contexto 2D del Canvas'));
          }

          // Dibujar en el canvas
          ctx.drawImage(img, 0, 0, width, height);

          // Exportar como blob WebP
          canvas.toBlob(
            (blob) => {
              if (!blob) {
                return reject(new Error('Error al convertir la imagen a formato WebP'));
              }
              const webpFile = new File([blob], filename.replace(/\.[^/.]+$/, "") + '.webp', {
                type: 'image/webp',
                lastModified: Date.now(),
              });
              resolve(webpFile);
            },
            'image/webp',
            quality
          );
        };

        img.onerror = (err) => reject(err);
        img.src = event.target?.result as string;
      };

      reader.onerror = (err) => reject(err);
      reader.readAsDataURL(file);
    });
  };

  /**
   * Genera una DataURL base64 para previsualización inmediata en el navegador
   */
  const getPreviewUrl = (file: File | Blob): Promise<string> => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result as string);
      reader.onerror = (err) => reject(err);
      reader.readAsDataURL(file);
    });
  };

  return {
    convertToWebP,
    getPreviewUrl,
  };
}

