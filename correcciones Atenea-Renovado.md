**correcciones Atenea-Renovado**

**12/7/26**



* el logo de atenea en el navbar esta muy pequeño no se logra observar bien en el logo de atenea
* 
* agrega una pasarela de pago stripe o pasarela de pago de visa/mastercad para cualquier tarjeta de crédito y debito al momento de pagar 
* 
* en el dashboard de admin añade para agregar productos las características que debe contener son: para usuario vera imágenes, descripción, 
* precio, disponible etc. para el admin debe tener editar esos campos, ver stock, cambiar precio, dar oferta, promoción
* 
* en el archivo de website/contact.php haz funcionar los campos de enviar un mensaje nombre, asunto, mensaje al correo pero dame ahi mismo donde añadir las credenciales de google para que funicone y añade un captcha para aumentar la seguridad 
* 
* en el registro añade campos nuevos como fecha de nacimiento, dui (usa la forma de como es el dui en el salvador (documento único de identidad)), numero de teléfono (añade código postales de países usa una api de ser necesario pero por determinado el del savaldor el +503), también añade campos como departamentos del país, municipios y los districtos de cada municipio y la dirección completa como campo opcional
* 
* hay detalles como fecha y hora desactualizado en el dashboard de admin 
* 
* por algún motivo los campos del sidebar salen cuadros blancos cuando esos solo se activan si abro esa etiqueta a diferencia del diseño original tal y como muestro en las imágenes eso se ve feo y horrible 
* 
* para cuando inicia como el bootsrap original del usuario ubicado en el src/estudiantes no tiene por que codex inventarse nuevas cosas o crear si no adaptar todo lso bootraps y eso para todos los bootraps que te he dejado como webiste, estudiantes, dashboard 

==========================================================================================================================================



credenciales:



***email***

email = ateneanaturopatia@gmail.com

Password = dozf fbqt mqjh pibh



***Client ID***

31864594137-qo4qtemaq27peijpn3mspmar9v2k0ijg.apps.googleusercontent.com



***stripe***

STRIPE\_SECRET\_KEY (TEST)=  



sk\_test\_51T09gWE8YH5P1jJkx2icAKfbPwb8K8bEeppD4Dzpkti0PRgqeBZU5x1g7oHLCLyqWOVfpn35xRILyinNVDY8CZtL00Yrlsb1bT



STRIPE\_PUBLISHABLE\_KEY =



pk\_test\_51T09gWE8YH5P1jJksI3DLYSKwIeLbdUYjJNEm9xGW3UcaA9qzwo1BXsOJz9VMVi89VE7bJh2apVXpoNLJ1B9CbeF00aCVIvyt2





&#x20;       'recaptcha\_site\_key' => entornoAtenea('RECAPTCHA_SITE_KEY'),

&#x20;       'recaptcha\_secret\_key' => entornoAtenea('RECAPTCHA_SECRET_KEY'),



==========================================================================================================================================



correcciones 2



* el login en la navbar sigue siendo muy pequeño no se nota nadota hazlo mas grande o ultra grande pero se tiene que notar el logo y que diga el atenea de la imagen en los 3 bootstrap del sistemas que están en el src/website, dashboard, estudiantes
* la pantalla de carga al entrar al login quítalo o hazlo mas acorde con los colores dorados y blanco, puedes utilizar verde oscuro también pero siempre usando esos 3 colores 
* ya puse mis credenciales de google\_client\_id pero aun me sale el "Google pendiente de configuración"
* haz funcionar el recuperar contraseña enviando un correo y siendo aja funcional y seguro
* agrega mas detalle al botón de iniciar sesión con google no se hazlo mas bonito e intuitivo 
* en el login añade que un texto o botón para volver al index ósea al homepage
* al iniciar sesión como estudiante no se parecen nada el que me haz hecho tu ni con el del bootstrap
* en el modulo de agregar productos me pide categoría pero no tengo espacio para añadir categorías tons agre un modulo para poner categorías editarlas y eliminarlas 
* haz que en todos los bootstraps de webistie, estudiantes y dashboard agregale un model o reutiliza alguno para ver los perfiles y el usuario pueda ver sus datos y que ahi también puedan cambiar contraseña y reciban un token si lo hacen y correo de confirmación de cualquier cambio q hagan en sus cuentas











