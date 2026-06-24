<style>
    .modal {
     width: 100%;
     height: 100%;
     background: rgba(0, 0, 0, 0.8);

     position: fixed; /* Fija el modal en la pantalla */
     top: 50%; /* Posiciona el centro del modal verticalmente en el 50% de la pantalla */
     left: 50%; /* Posiciona el centro del modal horizontalmente en el 50% de la pantalla */
     transform: translate(-50%, -50%); /* Mueve el modal hacia atrás para centrarlo */

     display: flex;
     align-items: center; /* Centra verticalmente */
     justify-content: center; /* Centra horizontalmente */

     animation: modal 1s 1s forwards;
     visibility: hidden;
     opacity: 0;
     z-index: 1;
     }

     .contenido {
         margin: auto;
         width: 600px; /* Ancho del contenido del modal */
         max-width: 500px; /* Ancho máximo del contenido */
         height: 620px;
         /*background: white;*/
         border-radius: 10px;
         padding: 5px; /* Agrega un poco de espacio interno */
         position: relative; /* Añade posicionamiento relativo */
         z-index: 2;
         display: flex;
         justify-content: center;
         align-items: center;
     }
     .contenido a{
        margin: -153px;
    border-bottom: solid 1px;
     }

     #cerrar {
         display: none;
         z-index: 1;
     }

     #cerrar + label {
         position: absolute;
         top: 10px;
         right: 10px;
         color: #fff;
         font-size: 25px;
         z-index: 3;
         height: 40px;
         width: 40px;
         line-height: 40px;
         border-radius: 50%;
         cursor: pointer;
         animation: modal 1s 1s forwards;
         visibility: hidden;
         opacity: 0;
     }

     #cerrar:checked + label,
     #cerrar:checked ~ .modal {
         display: none;
     }

     @keyframes modal {
         100% {
             visibility: visible;
             opacity: 1;
         }
     }
 </style>
 {{-- Modal --}}
 <input type="checkbox" id="cerrar">
<label for="cerrar" id="btn-cerrar">X</label>

 <div class="modal">
     <div class="contenido">
         <img src="/img/Comunicados/embalaje.png" alt="" style="width: 80%; height: auto;">
     </div>
 </div>
{{-- End Modal --}}
