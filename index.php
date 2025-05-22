<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fruit AI</title>
  <link rel="icon" href="imagenes/logo.png" type="image/png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="fruit.css">

</head>
<body>

    <div class="header">
      Fruit AI <i class="fas fa-apple-alt"></i>
      </div>

<div class="container mt-4">
  <div class="row align-items-center">

    <div class="col-md-4 text-center">
      <img src="imagenes/ejemplo.png" alt="Ejemplo" class="img-fluid mb-3" style="max-height: 200px;">
      <p class="info-text fst-italic">
        FruitAI es un software que procesa imágenes de frutas para clasificarlas en distintas categorías utilizando redes neuronales convolucionales.
      </p>
    </div>

    <div class="col-md-4">
      <div class="upload-box" id="drop-area">
        <input type="file" id="imageUpload" class="form-control mb-3" accept="image/*" style="display:none;">
        <button onclick="document.getElementById('imageUpload').click()" class="btn btn-outline-secondary mb-2">Subir imagen</button>
        <p>ó suelta fotos aquí</p>
        <img id="preview" class="img-fluid mt-2" style="max-height:150px; display:none;">
      </div>
      <div class="text-center mt-3">
        <button class="btn btn-analyze" onclick="analyzeImage()">Analizar</button>
      </div>
      <div id="spinner" class="spinner" style="display:none;"></div>
    </div>

    <div class="col-md-4">
      <div class="result-box">
        <h4>RESULTADOS</h4>
        <img id="defaultResultImg" src="imagenes/resultado.png" alt="Resultado por defecto" class="img-fluid mb-2" style="max-height: 80px;">
        <img id="resultImg" src="placeholder.png" alt="Resultado" class="img-fluid mb-2" style="max-height: 150px; display:none;">
        <p>Nombre de la fruta: <span id="fruitName">-</span></p>
        <p>Probabilidad: <span id="confidence">-</span></p>
      </div>
    </div>

  </div>
</div>
<br><br><br><br>
  <div class="footer">&copy; Ingeniería de Software</div>

    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.14.0/dist/tf.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.0"></script>

  <script>
    // Variable para almacenar el modelo MobileNet cargado
    let model;

    // Elementos del DOM con los que interactuaremos
    const dropArea = document.getElementById('drop-area'); // Área para arrastrar y soltar imágenes
    const fileInput = document.getElementById('imageUpload'); // Input de tipo archivo para seleccionar imágenes
    const previewImg = document.getElementById('preview'); // Imagen de vista previa antes de analizar
    const defaultResultImg = document.getElementById('defaultResultImg'); // Imagen por defecto en el cuadro de resultados
    const resultImg = document.getElementById('resultImg'); // Imagen mostrada en el cuadro de resultados después del análisis
    const spinner = document.getElementById('spinner'); // Elemento para mostrar la carga
    const fruitNameDisplay = document.getElementById("fruitName"); // Elemento para mostrar el nombre de la fruta
    const confidenceDisplay = document.getElementById("confidence"); // Elemento para mostrar la probabilidad

    // Objeto para traducir los nombres de las frutas del inglés al español
    const fruitTranslations = {
      "apple": "manzana",
      "banana": "plátano",
      "pineapple": "piña",
      "orange": "naranja",
      "strawberry": "fresa",
      "lemon": "limón",
      "peach": "durazno",
      "pear": "pera",
      "raspberry": "frambuesa",
      "mango": "mango",
      "cucumber": "pepino",
      "grape": "uva",
      "watermelon": "sandía",
      "cantaloupe": "melón",
      "plum": "ciruela"
    };

    // Evento para resaltar el área de carga al arrastrar una imagen sobre ella
    dropArea.addEventListener('dragover', (e) => {
      e.preventDefault(); // Previene el comportamiento por defecto del navegador
      dropArea.style.borderColor = '#8bc34a'; // Cambia el borde a verde
    });

    // Evento para resetear el borde del área de carga al salir de ella
    dropArea.addEventListener('dragleave', () => {
      dropArea.style.borderColor = '#ccc'; // Restaura el borde gris
    });

    // Evento para manejar la soltura de la imagen en el área de carga
    dropArea.addEventListener('drop', (e) => {
      e.preventDefault(); // Previene la apertura de la imagen en una nueva pestaña
      dropArea.style.borderColor = '#ccc'; // Restaura el borde gris

      const file = e.dataTransfer.files[0]; // Obtiene el primer archivo soltado
      if (file && file.type.startsWith('image/')) { // Verifica si el archivo es una imagen
        fileInput.files = e.dataTransfer.files; // Asigna los archivos soltados al input de tipo archivo
        showPreview(file); // Muestra la vista previa de la imagen
      }
    });

    // Evento para manejar el cambio en el input de tipo archivo (cuando se selecciona una imagen)
    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0]; // Obtiene el primer archivo seleccionado
      if (file) showPreview(file); // Muestra la vista previa si se seleccionó un archivo
    });

    // Función para mostrar la vista previa de la imagen seleccionada
    function showPreview(file) {
      const reader = new FileReader(); // Objeto para leer el contenido del archivo
      reader.onload = function (e) {
        previewImg.src = e.target.result; // Establece la fuente de la imagen de vista previa
        resultImg.src = e.target.result; // También establece la fuente de la imagen en el cuadro de resultados (inicialmente la misma)
        previewImg.style.display = 'block'; // Muestra la imagen de vista previa
        defaultResultImg.style.display = 'none'; // Oculta la imagen por defecto al cargar una nueva
        resultImg.style.display = 'block'; // Asegura que la imagen subida se muestre en el resultado
      };
      reader.readAsDataURL(file); // Lee el archivo como una URL de datos
    }

    // Función asíncrona para analizar la imagen subida
    async function analyzeImage() {
      const file = fileInput.files[0]; // Obtiene el archivo de la imagen subida
      if (!file) { // Si no se ha subido ninguna imagen
        alert('Por favor subí una imagen primero.');
        return; // Detiene la ejecución de la función
      }

      // Muestra el spinner de carga
      spinner.style.display = 'block';
      defaultResultImg.style.display = 'none'; // Asegura que la imagen por defecto esté oculta durante el análisis

      const imgElement = document.createElement('img'); // Crea un elemento imagen temporal
      imgElement.src = URL.createObjectURL(file); // Establece la fuente del elemento imagen con la imagen subida
      imgElement.width = 224; // Establece el ancho requerido por MobileNet
      imgElement.height = 224; // Establece la altura requerida por MobileNet

      imgElement.onload = async () => { // Cuando la imagen temporal se carga
        const predictions = await model.classify(imgElement); // Utiliza el modelo para clasificar la imagen
        spinner.style.display = 'none'; // Oculta el spinner después de la predicción
        if (predictions.length > 0) { // Si se encontraron predicciones
          const bestPrediction = predictions[0]; // Obtiene la predicción con mayor probabilidad
          const englishName = bestPrediction.className.toLowerCase(); // Obtiene el nombre en inglés en minúsculas
          let spanishName = "No identificado"; // Valor por defecto si no se encuentra traducción

          // Busca la traducción en español del nombre de la fruta
          for (let key in fruitTranslations) {
            if (englishName.includes(key)) { // Si el nombre en inglés contiene la clave
              spanishName = fruitTranslations[key]; // Asigna la traducción en español
              break; // Sale del bucle una vez que se encuentra la traducción
            }
          }

          fruitNameDisplay.innerText = spanishName; // Muestra el nombre de la fruta en español
          confidenceDisplay.innerText = (bestPrediction.probability * 100).toFixed(2) + "%"; // Muestra la probabilidad con dos decimales
          resultImg.style.display = 'block'; // Muestra la imagen analizada en el cuadro de resultados
        } else { // Si no se encontraron predicciones
          fruitNameDisplay.innerText = "No identificado";
          confidenceDisplay.innerText = "-";
          resultImg.style.display = 'none'; // Oculta la imagen de la fruta si no se identifica
          defaultResultImg.style.display = 'block'; // Muestra la imagen por defecto si no hay predicción
        }
      };
    }

    // Evento que se ejecuta cuando la ventana (la página) termina de cargar
    window.onload = async () => {
      model = await mobilenet.load(); // Carga el modelo MobileNet de forma asíncrona
      console.log("Modelo cargado correctamente."); // Mensaje en la consola para verificar la carga del modelo
    };
  </script>

</body>
</html>