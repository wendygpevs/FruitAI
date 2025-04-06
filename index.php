<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fruit AI</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="fruit.css">
  
</head>
<body>
    <div class="header">
        Fruit AI <i class ="fas fa-apple-alt"></i>
    </div>
    <br><br><br>

  <div class="container mt-4">
    <div class="row align-items-center">

      <!-- Columna Izquierda -->
      <div class="col-md-4 text-center">
        <img src="imagenes/ejemplo.png" alt="Ejemplo" class="img-fluid mb-3" style="max-height: 200px;">
        <p class="info-text">
          FruitAI es un software que procesa imágenes de frutas para clasificarlas en distintas categorías utilizando redes neuronales convolucionales.
        </p>
      </div>

      <!-- Columna Centro: Upload -->
      <div class="col-md-4">
      <div class="upload-box" id="drop-area">
        <input type="file" id="imageUpload" class="form-control mb-3" style="display:none;">
        <button onclick="document.getElementById('imageUpload').click()" class="btn btn-outline-secondary mb-2">Subir imagen</button>
        <p>ó suelta fotos aquí</p>
        <img id="preview" class="img-fluid mt-2" style="max-height:150px; display:none;">
      </div>
        <div class="text-center mt-3">
            <button class="btn btn-analyze" onclick="analyzeImage()">Analizar</button>
        </div>
      </div>
    

      <!-- Columna Derecha: Resultados -->
      <div class="col-md-4">
        <div class="result-box">
          <h4>RESULTADOS</h4>
          <!--Aqui se supone que debe mostrar la imagen que subi y abajo indicar el nombre y probabilidad-->
          <img src="placeholder.png" alt="Resultado" class="img-fluid mb-2" style="max-height: 150px;"> 
          <p style="text-align:justify; color: white;">Nombre de la fruta: <span id="fruitName">-</span></p>
          <p style="text-align:justify; color: white;">Probabilidad: <span id="confidence">-</span></p>
        </div>
      </div>

    </div>
  </div>

  <div class="footer">&copy; Ingeniería de Software</div>

  <script>
  const dropArea = document.getElementById('drop-area');
  const fileInput = document.getElementById('imageUpload');
  const previewImg = document.getElementById('preview');

  dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.style.borderColor = '#8bc34a';
  });

  dropArea.addEventListener('dragleave', () => {
    dropArea.style.borderColor = '#ccc';
  });

  dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.style.borderColor = '#ccc';

    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
      fileInput.files = e.dataTransfer.files;
      showPreview(file);
    }
  });

  fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (file) showPreview(file);
  });

  function showPreview(file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      previewImg.src = e.target.result;
      previewImg.style.display = 'block';
    };
    reader.readAsDataURL(file);
  }

  function analyzeImage() {
    const file = fileInput.files[0];
    if (!file) {
      alert('Por favor subí una imagen primero.');
      return;
    }

    // Simulación de resultado
    document.getElementById("fruitName").innerText = "Manzana";
    document.getElementById("confidence").innerText = "95%";
  }
</script>

</body>
</html>
