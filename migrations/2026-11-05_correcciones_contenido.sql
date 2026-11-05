-- Corrige un dato técnico: un globo estratosférico no pone un dispositivo en órbita.
-- No borra filas y se puede ejecutar más de una vez.
UPDATE `hitos_historicos`
SET `descripcion` = REPLACE(`descripcion`, 'fue puesto en órbita a través de un globo estratosférico', 'fue elevado con un globo estratosférico')
WHERE `descripcion` LIKE '%puesto en órbita a través de un globo%';
