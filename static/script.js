function actualizarLineNumbers() {
    const editor = document.getElementById('editor');
    const lineNumbers = document.getElementById('lineNumbers');
    
    if (!editor || !lineNumbers) return;
    
    const lines = editor.value.split('\n');
    const lineCount = lines.length;
    let numbersHTML = '';
    for (let i = 1; i <= lineCount; i++) {
        numbersHTML += i + '\n';
    }
    
    lineNumbers.textContent = numbersHTML;
}

function sincronizarScroll() {
    const editor = document.getElementById('editor');
    const lineNumbers = document.getElementById('lineNumbers');
    
    if (!editor || !lineNumbers) return;
    
    lineNumbers.scrollTop = editor.scrollTop;
}

document.addEventListener('DOMContentLoaded', function() {
    const editor = document.getElementById('editor');
    
    if (editor) {
        editor.addEventListener('input', actualizarLineNumbers);
        editor.addEventListener('scroll', sincronizarScroll);
        actualizarLineNumbers();
    }
});

function nuevoArchivo() {
    if (confirm('¿Desea crear un nuevo archivo? Se perderá el contenido actual si no lo ha guardado.')) {
        const editor = document.getElementById('editor');
        const consola = document.getElementById('consola');
        
        if (editor) editor.value = '';
        if (consola) consola.textContent = '';
        
        actualizarLineNumbers();
    }
}

function cargarArchivo(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const editor = document.getElementById('editor');
            if (editor) {
                editor.value = e.target.result;
                actualizarLineNumbers();
            }
        };
        reader.readAsText(file);
    }
    event.target.value = '';
}

function guardarArchivo() {
    const editor = document.getElementById('editor');
    const codigo = editor ? editor.value : '';
    
    if (!codigo.trim()) {
        alert('No hay código para guardar');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const inputAccion = document.createElement('input');
    inputAccion.name = 'accion';
    inputAccion.value = 'descargarCodigo';
    form.appendChild(inputAccion);
    
    const inputCodigo = document.createElement('input');
    inputCodigo.name = 'codigo';
    inputCodigo.value = codigo;
    form.appendChild(inputCodigo);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function limpiarConsola() {
    const consola = document.getElementById('consola');
    if (consola) {
        consola.textContent = '';
    }
}