<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Formato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente al Factory.
     *
     * @var string
     */
    protected $model = Producto::class;

    /**
     * Lista plana secuencial de todos los sabores de DiCreme con su categoría.
     * Al estar ordenada, garantizamos que se recorra uno por uno sin repetir.
     * @var array
     */
    protected static $catalogoSabores = [
        ['categoria' => 'Al agua', 'sabor' => 'Piña'],
        ['categoria' => 'Al agua', 'sabor' => 'Limón Manzana'],
        ['categoria' => 'Al agua', 'sabor' => 'Chirimoya Alegre'],
        ['categoria' => 'Al agua', 'sabor' => 'Limón Menta Jengibre'],
        
        ['categoria' => 'Leche de avena', 'sabor' => 'Piña Colada'],
        ['categoria' => 'Leche de avena', 'sabor' => 'Melón Tuna'],
        ['categoria' => 'Leche de avena', 'sabor' => 'Frambuesa'],
        ['categoria' => 'Leche de avena', 'sabor' => 'Chirimoya'],
        
        ['categoria' => 'Tradicional', 'sabor' => 'Chocolate'],
        ['categoria' => 'Tradicional', 'sabor' => 'Tropical'],
        ['categoria' => 'Tradicional', 'sabor' => 'Frutos del Bosque'],
        ['categoria' => 'Tradicional', 'sabor' => 'Pie de Limón'],
        ['categoria' => 'Tradicional', 'sabor' => 'Cookies and Cream'],
        ['categoria' => 'Tradicional', 'sabor' => 'Menta Chips'],
        ['categoria' => 'Tradicional', 'sabor' => 'Vainilla'],
        ['categoria' => 'Tradicional', 'sabor' => 'Lúcuma'],
        ['categoria' => 'Tradicional', 'sabor' => 'Cola de Mono'],
        ['categoria' => 'Tradicional', 'sabor' => 'Pistacho'],
        ['categoria' => 'Tradicional', 'sabor' => 'Pasas al Ron'],
        ['categoria' => 'Tradicional', 'sabor' => 'Mora'],
        ['categoria' => 'Tradicional', 'sabor' => 'Suspiro Limeño'],
        ['categoria' => 'Tradicional', 'sabor' => 'Chocolate Avellana'],
        ['categoria' => 'Tradicional', 'sabor' => 'Cheesecake Frutos Rojos'],
        ['categoria' => 'Tradicional', 'sabor' => 'Frutilla'],
        ['categoria' => 'Tradicional', 'sabor' => 'Dulce de Leche'],
        ['categoria' => 'Tradicional', 'sabor' => 'Banana Split'],
        ['categoria' => 'Tradicional', 'sabor' => 'Mango'],
        ['categoria' => 'Tradicional', 'sabor' => 'Arroz con leche'],
        ['categoria' => 'Tradicional', 'sabor' => 'Tres Leches'],
        
        ['categoria' => 'Sin azúcar', 'sabor' => 'Chocolate sin Azúcar'],
        ['categoria' => 'Sin azúcar', 'sabor' => 'Pistacho sin Azúcar'],
        ['categoria' => 'Sin azúcar', 'sabor' => 'Frutos del Bosque sin Azúcar'],
        ['categoria' => 'Sin azúcar', 'sabor' => 'Vainilla'],
    ];

    /**
     * Precios ordenados emparejados con la secuencia de formatos:
     * Index 0 (10L) -> 23900
     * Index 1 (5L)  -> 16900
     * Index 2 (2.5L)-> 8900
     * Index 3 (1L)  -> 3900
     * @var array
     */
    protected static $precio_producto = [23900, 16900, 8900, 3900];

    /**
     * Contador estático de ejecuciones. 
     * Rastreará exactamente en qué número de iteración va el Seeder.
     * @var int
     */
    protected static $contadorGlobal = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1. Obtener de PostgreSQL la lista completa de formatos reales ordenados ('10L', '5L', '2.5L', '1L')
        $formatosDisponibles = Formato::whereIn('nombre_formato', ['10L', '5L', '2.5L', '1L'])
            ->orderBy('id', 'asc')
            ->get();

        $totalFormatos = $formatosDisponibles->count() ?: 4;

        // 2. CÁLCULO MATEMÁTICO INMUTABLE DE LOS ÍNDICES SECUENCIALES
        // intdiv hace una división entera: para las iteraciones 0,1,2,3 dará como resultado 0 (Sabor 1).
        $indiceSabor = intdiv(self::$contadorGlobal, $totalFormatos) % count(self::$catalogoSabores);
        
        // El módulo % cambia en cada paso (0, 1, 2, 3, 0, 1, 2, 3...) vinculando consecutivamente los formatos.
        $posicionFormato = self::$contadorGlobal % $totalFormatos;

        // 3. ASIGNACIÓN DEL SABOR Y LA CATEGORÍA CORRESPONDIENTE
        $itemSabor = self::$catalogoSabores[$indiceSabor];
        
        // Buscamos el ID real de la categoría en la base de datos
        $categoriaBD = Categoria::where('nombre_categoria', $itemSabor['categoria'])->first();
        $idCategoria = $categoriaBD?->id ?? Categoria::factory();

        // 4. ASIGNACIÓN DEL FORMATO CORRESPONDIENTE
        if ($formatosDisponibles->isNotEmpty()) {
            $idFormato = $formatosDisponibles->get($posicionFormato)->id;
        } else {
            $idFormato = Formato::factory();
        }

        // CORRECCIÓN: Seleccionar el precio correspondiente de forma secuencial exacta usando $posicionFormato
        // Si $posicionFormato es 0, toma self::$precio_producto[0] (23900), etc.
        $precioElegido = self::$precio_producto[$posicionFormato] ?? 3900;

        // Incrementamos el contador para preparar el siguiente registro
        self::$contadorGlobal++;

        return [
            'id_categoria'    => $idCategoria,
            'id_formato'      => $idFormato,
            'nombre_producto' => $itemSabor['sabor'], 
            'precio_producto' => $precioElegido, // Ahora va perfectamente de la mano con el tamaño del envase
        ];
    }
}