function ComprobarBtn(f) {
    var ok = false;
    if(f.elements[0].value == "" || f.elements[0].value == "" ){
        console.log('algo'+ f.elements[0].value);
        alert('Las fechas seleccionada no son correctas \n Final tiene que ser fecha superior a inicio');
        ok = false;
    }
    if (f.elements[2].checked === true){
        alert('Seleccionaste emitido');
        document.getElementById('id_libroIva').action = "emitido.php";
        ok = true;

    }
    if (f.elements[3].checked === true){
        alert('Seleccionaste soportado');
        document.getElementById('id_libroIva').action = "soportado.php";
        ok = true;


    }
    alert('Pulsaste btn');
    return ok;
}


function anular(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    return (tecla != 13);
}
