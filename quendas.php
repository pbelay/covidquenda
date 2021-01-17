
<p><b> Importante:</b> Os datos teñen que estar en CSV e nas columnas indicadas. Podes ver un exemplo de ficheiro correcto na seguinte ligazón: <a href="datos.csv">Datos de exemplo</a> </p>

<ul>
  <li> Campo 1: Nome</li>
  <li> Campo 2: Apelido 1</li>
  <li> Campo 3: Apelido 2</li>
  <li> Campo 4: DNI</li>
  <li> Campo 5: E-mail do alumno</li>
  <li> Campo 6: Cadea Si ou Non que nos indica se quere xustificante. Isto ainda está pendente de implementar. </li>
  <li> Campo 7: Turno. Agarda un número 1 ou 2 </li>
  <li> Campo 8: Número do ordenador a empregar polo alumno/a</li>
  <li> Campo 9: Dia do exame </li>
  <li> Campo 10: Franxa horario do exame, por exemplo: 19:20 - 20:35</li>
</ul>
<p>Esta ferramenta únicamente funciona co servizo de correo de edu.xunta.gal </p>

<br>
<form action="upload.php" method="post" enctype="multipart/form-data">
    <b>Formulario para xerar quendas:</b> 
    <br>
    <label for="mailProfe">Email do profesor da Xunta: </label>
    <input  id="mailProfe" type="text" name="mailProfe" size="20" maxlength="100">
    <br>
    
    <label for="mailSenha">Clave do email da Xunta: </label>
    <input id="mailSenha" type="text" name="mailSenha" size="20" maxlength="100">
    <br>
    
      <label for="modulo">Módulo: </label>
    <input id="modulo" type="text" name="modulo" size="120" maxlength="100">
    <br>
    
    
    <label for="aula">Aula do exame: </label>
    <input id="aula" type="text" name="aula" size="20" maxlength="100">
    <br>

	<label for="dia">Dia sinatura: </label>
    <input id="dia" type="number" name="dia" size="20" value="10" maxlength="100">
    <br>
    
    <label for="mes">Mes sinatura: </label>
    <input id="mes" type="text" name="mes" size="20" value="decembro"  maxlength="100">
    <br>
    <br>
    
    <label for="modalidade">modalidade: </label>
    <input id="modalidade" type="text" name="modalidade" value="distancia" size="20" maxlength="100">
    <br>
    <br>

   <label for="nomeprofe">Nome e apelidos do profesor: </label>
    <input id="nomeprofe" type="text" name="nomeprofe" size="80" maxlength="100">
    <br>
    <br>




    
    <input type="hidden" name="MAX_FILE_SIZE" value="100000">
    <b>Arquivo CSV cos datos: </b>
    <br>
    <input name="userfile" type="file">
    <br>
    <br>
    <input type="submit" value="Enviar">
</form>
