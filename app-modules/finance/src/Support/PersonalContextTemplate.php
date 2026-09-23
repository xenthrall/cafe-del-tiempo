<?php

namespace Tequia\Finance\Support;

use Tequia\Finance\Enums\CategoryType;

/**
 * Árbol de categorías de ejemplo para el contexto "Personal" que se ofrece a
 * un usuario nuevo (ver `CreateSampleContextAction`), para que pueda empezar
 * a registrar movimientos sin tener que diseñar su propia estructura de
 * categorías desde cero.
 */
class PersonalContextTemplate
{
    /**
     * @return array<string, array<string, list<string>>>
     */
    public static function categories(): array
    {
        return [
            CategoryType::Expense->value => [
                'Alimentación' => ['Mercado', 'Restaurantes', 'Cafés / Snacks', 'Domicilios', 'Comida rápida'],
                'Vivienda y Hogar' => ['Arriendo', 'Servicios públicos', 'Internet', 'Telefonía', 'Mantenimiento', 'Reparaciones', 'Muebles', 'Artículos para el hogar'],
                'Transporte' => ['Transporte Público', 'Taxi / Apps', 'Combustible', 'Parqueadero', 'Peajes', 'Mantenimiento'],
                'Salud' => ['Consultas médicas', 'Medicamentos', 'Odontología', 'Exámenes', 'Seguros de salud'],
                'Cuidado Personal' => ['Afeitado', 'Cuidado del cabello', 'Higiene y Aseo', 'Cuidado de la piel', 'Gimnasio'],
                'Vestuario' => ['Ropa', 'Calzado', 'Ropa deportiva', 'Accesorios'],
                'Educación' => ['Cursos', 'Libros', 'Material educativo', 'Certificaciones', 'Exámenes'],
                'Suscripciones y Servicios' => ['Streaming', 'Música', 'Software', 'Cloud', 'Hosting / Dominios', 'IA'],
                'Entretenimiento y Ocio' => ['Cine', 'Videojuegos', 'Hobbies', 'Eventos', 'Viajes'],
                'Compras' => ['Electrónica', 'Tecnología', 'Herramientas', 'Compras online', 'Otros bienes'],
                'Familia y Relaciones' => ['Ayuda familiar', 'Celebraciones', 'Eventos familiares'],
                'Finanzas' => ['Comisiones bancarias', 'Intereses', 'Cuotas de manejo', 'Otros costos financieros'],
                'Impuestos y Obligaciones' => ['Renta', 'Vehicular', 'Predial', 'Multas', 'Otros'],
                'Regalos y Donaciones' => ['Regalos', 'Donaciones', 'Aportes'],
            ],
            CategoryType::Income->value => [
                'Ingresos Laborales' => ['Salario', 'Honorarios', 'Freelance', 'Bonificaciones', 'Comisiones'],
                'Actividades' => ['Desarrollo de software', 'Servicios técnicos', 'Venta de productos', 'Otros trabajos'],
                'Rendimientos e Inversiones' => ['Intereses', 'Dividendos', 'Rendimientos', 'Ganancias de inversión'],
                'Otros Ingresos' => ['Reembolsos', 'Devoluciones', 'Regalos recibidos', 'Otros'],
            ],
        ];
    }
}
