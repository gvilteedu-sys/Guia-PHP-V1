<?php
declare(strict_types=1);
function calcularTotal(int $cantidad, float $precioUnitario): float
{
    return $cantidad * $precioUnitario;
}
// Esto funcionará correctamente:
echo calcularTotal(3, 19.99); // Retorna 59.97
// Esto lanzará un TypeError no capturado si strict_types está activo:
// echo calcularTotal("3", "19.99"); // TypeError: Argument 1 must be of type int, string given
