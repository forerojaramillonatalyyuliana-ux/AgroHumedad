-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-10-2026 a las 06:14:51
-- Versión del servidor: 10.11.16-MariaDB
-- Versión de PHP: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_de_riesgo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto`
--

CREATE TABLE `contacto` (
  `id_contacto` int(11) NOT NULL,
  `id_u` int(11) DEFAULT NULL,
  `nombre` varchar(80) NOT NULL DEFAULT '',
  `correo` varchar(100) NOT NULL DEFAULT '',
  `mensaje` varchar(500) NOT NULL DEFAULT '',
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contacto`
--

INSERT INTO `contacto` (`id_contacto`, `id_u`, `nombre`, `correo`, `mensaje`, `fecha`) VALUES
(3, NULL, 'cindy bedoya', 'cindy@example.com', 'hgjkkj', '2026-10-24 16:18:13'),
(4, NULL, 'Administrador Sistema', 'admin@sistemaderiesgo.com', 'hola', '2026-10-24 16:41:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lectora`
--

CREATE TABLE `lectora` (
  `id_lectora` int(11) NOT NULL,
  `id_u` int(11) DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `humedad` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lectora`
--

INSERT INTO `lectora` (`id_lectora`, `id_u`, `fecha_hora`, `humedad`) VALUES
(3, NULL, '2026-10-24 14:32:05', 81.50),
(4, NULL, '2026-10-24 14:36:01', 61.50),
(5, NULL, '2026-10-24 14:37:54', 61.50),
(20, NULL, '2026-10-24 16:20:05', 15.00),
(21, NULL, '2026-10-24 16:42:03', 85.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id_noticia` int(11) NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `categoria` varchar(80) NOT NULL DEFAULT 'Actualidad',
  `resumen` varchar(400) NOT NULL,
  `contenido` text NOT NULL,
  `imagen_url` varchar(500) DEFAULT NULL,
  `fuente_url` varchar(500) DEFAULT NULL,
  `estado` enum('Borrador','Publicado') NOT NULL DEFAULT 'Borrador',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_publicacion` datetime DEFAULT NULL,
  `id_u` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id_noticia`, `titulo`, `categoria`, `resumen`, `contenido`, `imagen_url`, `fuente_url`, `estado`, `fecha_creacion`, `fecha_publicacion`, `id_u`) VALUES
(1, 'IDEAM confirma el fenómeno de El Niño para 2026-2027', 'Clima y agricultura', 'Colombia debe prepararse para los posibles efectos del fenómeno de El Niño sobre la disponibilidad de agua, las temperaturas y las actividades agrícolas.', 'El Instituto de Hidrología, Meteorología y Estudios Ambientales (IDEAM) confirmó el 8 de octubre de 2026 las condiciones técnicas del fenómeno de El Niño en Colombia. Este fenómeno puede modificar los patrones de lluvia y temperatura, con consecuencias diferentes según la región.\r\n\r\nPara los agricultores, es importante prestar atención a la humedad del suelo, las precipitaciones y las condiciones de los cultivos. La disponibilidad de agua puede cambiar durante los próximos meses y afectar las actividades de siembra, riego y mantenimiento de las plantas.\r\n\r\nEntre las medidas preventivas se encuentran revisar los sistemas de almacenamiento de agua, evitar desperdicios durante el riego y consultar los pronósticos oficiales antes de tomar decisiones agrícolas.\r\n\r\nLa preparación debe adaptarse a las condiciones de cada territorio. El fenómeno no significa que dejará de llover en todo el país, por lo que es necesario consultar la información climática correspondiente a cada región.', 'uploads/noticias/noticia_dba390d0b7ab56bd16adae8f.jpg', 'https://www.ideam.gov.co/sala-de-prensa/noticia/ideam-confirma-el-fenomeno-de-el-nino-y-ungrd-articula-la-preparacion-nacional-para-afrontar-sus?trk=article-ssr-frontend-pulse_little-text-block', 'Publicado', '2026-10-24 16:44:00', '2026-10-24 16:44:00', NULL),
(2, 'Cómo conservar la humedad del suelo durante las temporadas secas', 'Manejo del suelo', 'Las coberturas vegetales, el uso responsable del agua y la preparación adecuada del terreno ayudan a conservar la humedad necesaria para los cultivos.', 'Conservar la humedad del suelo es una tarea importante para mantener el desarrollo de los cultivos, especialmente durante los periodos con pocas lluvias. Cuando el agua se evapora rápidamente, las plantas pueden tener dificultades para absorber la cantidad que necesitan.\n\nUna práctica útil consiste en mantener una cobertura sobre el suelo mediante materiales vegetales apropiados, como paja o residuos de cosecha sanos. Esta cobertura puede ayudar a disminuir la evaporación y proteger la superficie frente al impacto directo de la lluvia.\n\nTambién es conveniente evitar remover el suelo innecesariamente, revisar las condiciones de humedad antes de regar y conservar el agua disponible para los momentos en que el cultivo realmente la necesita.\n\nCada terreno tiene características diferentes. Por eso, las acciones deben ajustarse al tipo de suelo, al cultivo y a las condiciones climáticas de la zona.', 'uploads/noticias/noticia_162521596a71638ab6faaeb8.jpg', 'https://www.ideam.gov.co/file-download/download/public/20418', 'Publicado', '2026-10-24 16:44:00', '2026-10-24 16:44:00', NULL),
(3, 'Riego eficiente: cuándo y cuánto necesitan agua las plantas', 'Riego sostenible', 'Observar la humedad del suelo y las necesidades del cultivo permite organizar mejor el riego y reducir el desperdicio de agua.', 'Regar los cultivos no consiste únicamente en agregar agua de manera frecuente. Para aprovechar este recurso, es importante considerar la humedad del suelo, el tipo de planta, su etapa de crecimiento y las condiciones del ambiente.\r\n\r\nUn suelo demasiado seco puede dificultar la absorción de agua por las raíces, mientras que un exceso de humedad puede reducir la aireación y favorecer algunos problemas sanitarios. Por esta razón, observar las condiciones del terreno ayuda a tomar decisiones más adecuadas.\r\n\r\nTambién es recomendable revisar las pérdidas en mangueras y conexiones, utilizar sistemas de riego apropiados para el cultivo y evitar aplicar agua cuando no sea necesaria.\r\n\r\nLa medición de la humedad y el seguimiento de las condiciones del cultivo pueden complementar la experiencia de los agricultores. El objetivo es utilizar el agua de manera responsable y mantener condiciones favorables para el crecimiento de las plantas.', 'uploads/noticias/noticia_8de287760a8540427a0cc300.jpg', 'https://www.fao.org/family-farming/detail/es/c/1737825/', 'Publicado', '2026-10-24 16:44:00', '2026-10-24 16:44:00', NULL),
(4, 'La agricultura digital como apoyo para el uso responsable del agua', 'Tecnología agrícola', 'Las herramientas digitales permiten organizar mediciones y consultar información que facilita el seguimiento de las condiciones de los cultivos.', 'La agricultura digital incorpora herramientas tecnológicas para apoyar las actividades del campo. Entre sus aplicaciones se encuentran el registro de datos, la consulta de información climática y el seguimiento de las condiciones del suelo.\r\n\r\nMedir o registrar la humedad permite reconocer cambios en el terreno y comparar las condiciones observadas en diferentes momentos. Esta información puede ser útil para identificar cuándo se requiere una revisión del cultivo o cuándo conviene evaluar las necesidades de riego.\r\n\r\nNo obstante, la tecnología debe utilizarse como apoyo y no como sustituto de la observación directa. Los resultados deben interpretarse considerando las características del cultivo, el tipo de suelo y las condiciones ambientales.\r\n\r\nLos proyectos de agricultura digital pueden contribuir a que pequeños productores, estudiantes y comunidades rurales comprendan mejor la información agrícola y fortalezcan sus prácticas de cuidado del agua.', 'uploads/noticias/noticia_0e3ea58f72d2e7653325f66f.jpg', 'https://repository.agrosavia.co/handle/20.500.12324/39007', 'Publicado', '2026-10-24 16:44:00', '2026-10-24 16:44:00', NULL),
(5, 'Consultar los pronósticos climáticos ayuda a planificar las labores agrícolas', 'Pronóstico climático', 'Los boletines agroclimáticos permiten revisar las previsiones de lluvia y otras variables relevantes antes de organizar las actividades del campo.', 'La planificación agrícola requiere considerar las condiciones actuales del terreno y los cambios que puede presentar el clima. Consultar pronósticos y boletines agroclimáticos ayuda a identificar posibles escenarios y preparar las labores del cultivo.\r\n\r\nEl IDEAM publica información periódica sobre las condiciones meteorológicas y las previsiones para diferentes regiones del país. Estos reportes incluyen datos que pueden apoyar la planificación de las actividades agrícolas y el seguimiento de la disponibilidad de agua.\r\n\r\nAntes de sembrar, aplicar riego o realizar labores en el terreno, es recomendable revisar la información más reciente y contrastarla con las condiciones observadas en la finca.\r\n\r\nLos pronósticos no representan una garantía absoluta sobre lo que ocurrirá en cada lugar. Por ello, deben utilizarse como una herramienta de apoyo y actualizarse a medida que se publiquen nuevos informes.', 'uploads/noticias/noticia_bcd1e1f8ebeaa46aa0f3bda7.jpg', 'https://www.ideam.gov.co/sala-de-prensa/boletines/Bolet%C3%ADn-de-predicci%C3%B3n-clim%C3%A1tica', 'Publicado', '2026-10-24 16:44:00', '2026-10-24 16:44:00', NULL),
(6, 'Seguir la humedad del suelo ayuda a reconocer riesgos para los cultivos', 'Prevención de riesgos', 'Registrar la humedad y observar el estado del terreno permite identificar cambios que requieren atención y tomar medidas preventivas.', 'La humedad del suelo influye en el desarrollo de las raíces y en la disponibilidad de agua para las plantas. Cuando las condiciones cambian, los cultivos pueden necesitar ajustes en el manejo del riego o medidas para mejorar el drenaje.\r\n\r\nEl seguimiento periódico permite reconocer señales como el secado de la superficie, la presencia de agua acumulada o los cambios visibles en las plantas. Estos indicios deben analizarse junto con la información del clima y las características del terreno.\r\n\r\nEn una huerta escolar o productiva, llevar un registro de las observaciones facilita comparar resultados, reconocer patrones y evaluar las acciones realizadas. Los datos pueden organizarse en tablas o plataformas digitales para consultar su evolución.\r\n\r\nLa prevención requiere observación constante y decisiones acordes con cada cultivo. Un registro ordenado contribuye a comprender mejor las condiciones ambientales y a cuidar los recursos disponibles.', 'uploads/noticias/noticia_1ac8e0692edcfa8c357fc681.jpg', 'https://www.ideam.gov.co/sala-de-prensa/boletines/Bolet%C3%ADn-Semanal-para-el-Sector-Agr%C3%ADcola-%28BSA%29', 'Publicado', '2026-10-24 16:44:00', '2026-10-24 16:44:00', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_u` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `rol` varchar(30) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_u`, `nombre`, `apellido`, `tel`, `rol`, `correo`, `password`) VALUES
(2, 'Nataly', 'Jaramillo', '2333444', 'Usuario', 'forerojaramillonatalyyuliana@ieru.edu.co', '$2y$12$/ynIpSd33u.T2CkkzYQCv.7fX/FG8sb8QZhqJDBR.Zl.UuCxzZr5i'),
(3, 'Administrador', 'Sistema', '1000000000', 'Administrador', 'admin@sistemaderiesgo.com', '$2y$10$/Wj8bZZME0vNBn22v3my8.lZomg9IMtbvk4WlibjsaLE6Ut09p4Zu'),
(5, 'cindy', 'bedoya', '3054564', 'Usuario', 'cindy@example.com', '$2y$10$SO6mn5W8Hgofjkv/L4aLnO8f25dB/wyyI67U.bedy7WyhHKVIFBYe');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `contacto`
--
ALTER TABLE `contacto`
  ADD PRIMARY KEY (`id_contacto`),
  ADD KEY `idx_contacto_usuario` (`id_u`);

--
-- Indices de la tabla `lectora`
--
ALTER TABLE `lectora`
  ADD PRIMARY KEY (`id_lectora`),
  ADD KEY `idx_lectora_usuario` (`id_u`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id_noticia`),
  ADD KEY `idx_noticias_estado_fecha` (`estado`,`fecha_publicacion`),
  ADD KEY `idx_noticias_usuario` (`id_u`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_u`),
  ADD UNIQUE KEY `uk_usuario_correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `contacto`
--
ALTER TABLE `contacto`
  MODIFY `id_contacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `lectora`
--
ALTER TABLE `lectora`
  MODIFY `id_lectora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id_noticia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_u` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `contacto`
--
ALTER TABLE `contacto`
  ADD CONSTRAINT `fk_contacto_usuario` FOREIGN KEY (`id_u`) REFERENCES `usuario` (`id_u`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `lectora`
--
ALTER TABLE `lectora`
  ADD CONSTRAINT `fk_lectora_usuario` FOREIGN KEY (`id_u`) REFERENCES `usuario` (`id_u`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `fk_noticias_usuario` FOREIGN KEY (`id_u`) REFERENCES `usuario` (`id_u`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
