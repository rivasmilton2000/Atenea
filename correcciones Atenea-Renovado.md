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





&#x20;       'recaptcha\_site\_key' => entornoAtenea('6Lez1lAtAAAAAMldVwHgvaHRz2yHEuJDr0ol2htz'),

&#x20;       'recaptcha\_secret\_key' => entornoAtenea('6Lez1lAtAAAAABJoavF0sq6LZ13qebfXydqNvqdq'),



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



==========================================================================================================================================







13/7/26

correcciones 3

* en la parte final del index website donde sale "© 2026 Atenea Escuela de Naturopatía Holística Todos los derechos reservados Plantilla base por BootstrapMade, distribuida por ThemeWagon y adaptada para Atenea."hay un espacio en blanco al fializar la pagina entonces quítame ese espacio vacio, para mayor referencias te dejo una imagen
* en el panel de control cuando le doy click a la imagen de mi perfil donde se supone que me debe de abrir el model de mi perfil solo me abre la imagen de perfil en grande en toda la cara, soluciona ese error te deje una imagen para mayor referencia cabe recalcar que eso pasa en el panel de administración es decir src/dashboard y de paso revisa eso en el website y estudiantes por si acaso
* cuando le doy a ver sitio quiero que en la misma pestaña me rediriga a la vista website no me que me habrá otra pestaña en el navegador
* hay espacios en blanco por arriba y por abajo en el login y en el registro
* ahora en el código quiero que me respete un orden al crear nuevos archivos en sus respectivas carpetas y de no haberlo crea nueva carpetas
* para poner las keys de recapchat, client\_id de google, las keys de stripe y las de email sea todo en un mismo archivo donde funciones esas claves en diferentes clases en un solo archivo dentro de la carpeta includes
* el orden del proyecto debe ser profesional

==========================================================================================================================================

14/07/26



correcciones 4

* agrega sweets alerts o un tipo de alerta decentes para el sistema 
* el apartado para ver el producto hazlo ver mas atractivo y bonito y mejórame el botón de compra y al usuario que realizo la compra reciba un correo de confirmación de su compra junto a su factura
* y si puedes a lo de compras agregale que son pagos seguros con visa, MasterCard sus logos ósea que se vea bonito
* hay un bug en el modulo de dashboard de admin que cuando entro a cualquier otro modulo se pierden algunos de la sidebar no se ven todos arregla eso y que funcione universalmente para nuevos modulos de mas adelante
* en el modulo de usuarios haz q el admin pueda ver una bitácora de movimientos de cada usuario q realiza en la plataforma en caso de algún fallo
* el administrador puede ver detalles de las cuentas como sus movimientos de compras y método de pago (ojo obviamente los datos de sus tarjetas de crédito no mas que sus utlimo 4 dígitos) detalles, como sus nombres, certifiaciones activas, direcciones, dui, etc 
* el administrador puede cambiar el rol de una cuenta es decir cambiarlo de usuario/docente/admin cambiarse solo a su propio usuario no  lo puede cambiar pero a los demás si los puede asender como desender 
* el admin puede editar, dar aviso del usuario tiene que hacer un cambio en su cuentas, el usuario puede cambiar la contraseña de una cuenta pero necesitara un proceso de cambio de contraseña como que reciba un correo confirmando el cambio con un código de contraseña que debe de dar al admin para que se haga el cambio en caso que el usuario se comunique para pedir un cambio de contraseña
* el admin puede eliminar cuentas y las inactivas por mas de 3 años deber ser eliminadas automáticas
* a cualquier tipo de correos que envies o recibas dale un diseño bonito y atractivo respetando los colores que identifican a atenea
* en el login, dashboard de src de admin y el bootraps de estudiantes en el src hay detalles de color azul entonces siguiendo respetando la estética de la website quiero que me cambies los colores y esos detalles azules a los colores que definen atenea en tonos dorados, verdes oscuro (de preferencia pero prueba con combinaciones geniales y atractivas)

