<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListProductRequest;
use App\Http\Requests\SellProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/** Controller del CRUD de productos. */
class ProductController extends Controller
{
    public function index(ListProductRequest $request): JsonResponse
    {
        $query = Product::query()->orderBy('id');

        $category = $request->validated('categoria');

        if ($category !== null) {
            $query->where('categoria', $category);
        }

        // Eloquent convierte la colección de productos a JSON automáticamente.
        return ApiResponse::success($query->get());
    }

    public function show(Product $product): JsonResponse
    {
        // Laravel encontró Product usando el {product} de la ruta (model binding).
        return ApiResponse::success($product);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['activo'] = true;
        $product = Product::query()->create($data);

        return ApiResponse::success($product, 'Producto creado.', 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        if ($data === []) {
            return ApiResponse::error('Mandá al menos un campo para modificar.', 422);
        }

        $product->update($data);

        return ApiResponse::success($product->refresh(), 'Producto actualizado.');
    }

    public function destroy(Product $product): JsonResponse
    {
        if ($product->stock > 0) {
            return ApiResponse::error('No se puede borrar un producto que todavía tiene stock.', 409);
        }

        $product->delete();

        return ApiResponse::success(null, 'Producto eliminado.');
    }

    public function sell(SellProductRequest $request, Product $product): JsonResponse
    {
        $quantity = (int) $request->validated('cantidad');

        if (! $product->activo) {
            return ApiResponse::error('El producto está inactivo.', 409);
        }

        if ($product->stock < $quantity) {
            return ApiResponse::error('No hay stock suficiente para realizar la venta.', 409);
        }

        $product->decrement('stock', $quantity);

        return ApiResponse::success($product->refresh(), 'Venta realizada.');
    }
}
