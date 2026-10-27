
const controlHumedad = document.getElementById("humedad");
const valorHumedad = document.getElementById("valorHumedad");
const porcentajeResultado = document.getElementById("porcentajeResultado");
const estadoSuelo = document.getElementById("estadoSuelo");
const barraHumedad = document.getElementById("barraHumedad");
const recomendacion = document.getElementById("recomendacion");
const estadoRiego = document.getElementById("estadoRiego");
const iconoEstado = document.getElementById("iconoEstado");

function actualizarSimulacion() {
    const humedad = Number(controlHumedad.value);

    valorHumedad.textContent = humedad;
    porcentajeResultado.textContent = humedad + "%";
    barraHumedad.style.width = humedad + "%";

    if (humedad < 30) {
        estadoSuelo.textContent = "Suelo seco";
        barraHumedad.style.background = "#d89a43";
        iconoEstado.style.background = "#fff0d9";
        iconoEstado.style.color = "#c17c31";
        iconoEstado.innerHTML = '<i class="fa-solid fa-sun"></i>';

        recomendacion.textContent =
            "El suelo presenta poca humedad. Revisa las necesidades del cultivo y considera regar si las condiciones lo requieren.";

        estadoRiego.innerHTML =
            '<i class="fa-solid fa-faucet-drip"></i>' +
            '<span>Riego: revisar si el cultivo necesita agua</span>';

        estadoRiego.style.background = "#fff3df";
        estadoRiego.style.color = "#956323";

    } else if (humedad <= 70) {
        estadoSuelo.textContent = "Humedad intermedia";
        barraHumedad.style.background = "#62a95b";
        iconoEstado.style.background = "#e6f3df";
        iconoEstado.style.color = "#4a914b";
        iconoEstado.innerHTML = '<i class="fa-solid fa-seedling"></i>';

        recomendacion.textContent =
            "El suelo tiene un nivel intermedio de humedad según los rangos de esta simulación. Continúa observando las condiciones del cultivo antes de regar.";

        estadoRiego.innerHTML =
            '<i class="fa-solid fa-circle-check"></i>' +
            '<span>Riego: seguir monitoreando</span>';

        estadoRiego.style.background = "#eaf5e5";
        estadoRiego.style.color = "#38663e";

    } else {
        estadoSuelo.textContent = "Suelo muy húmedo";
        barraHumedad.style.background = "#4c91c5";
        iconoEstado.style.background = "#e2f1fc";
        iconoEstado.style.color = "#397eb1";
        iconoEstado.innerHTML = '<i class="fa-solid fa-cloud-rain"></i>';

        recomendacion.textContent =
            "El suelo presenta un nivel alto de humedad. Revisa el drenaje y evita agregar más agua sin comprobar las necesidades del cultivo.";

        estadoRiego.innerHTML =
            '<i class="fa-solid fa-ban"></i>' +
            '<span>Riego: evitar agregar agua sin verificar</span>';

        estadoRiego.style.background = "#e7f3fc";
        estadoRiego.style.color = "#326f9e";
    }
}

controlHumedad.addEventListener("input", actualizarSimulacion);

document.getElementById("btnSeco").addEventListener("click", () => {
    controlHumedad.value = 15;
    actualizarSimulacion();
});

document.getElementById("btnAdecuado").addEventListener("click", () => {
    controlHumedad.value = 50;
    actualizarSimulacion();
});

document.getElementById("btnHumedo").addEventListener("click", () => {
    controlHumedad.value = 85;
    actualizarSimulacion();
});

// Mostrar el resultado inicial.
actualizarSimulacion();
