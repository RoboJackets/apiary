<?php
/**
 * Created by PhpStorm.
 * User: ross
 * Date: 10/22/17
 * Time: 8:25 PM
 */
namespace App\Traits;

use League\Fractal\TransformerAbstract;
use Spatie\Fractalistic\ArraySerializer;
use Spatie\Fractal\Fractal;

trait FractalResponse
{
    /**
     * @param $data mixed Collection or single item to be Fractal-ized
     * @param TransformerAbstract $transformer Fractal Transformer
     * @param string $include Related models to include (Optional)
     * @return \Spatie\Fractalistic\Fractal
     */
    public function fractalResponse($data, TransformerAbstract $transformer, $include = null)
    {
        $isCollection = is_a($data, "Illuminate\Support\Collection");
        $response = ($isCollection) ? Fractal::create()->collection($data) : Fractal::create()->item($data);
        $response->transformWith($transformer)
            ->serializeWith(new ArraySerializer())
            ->parseIncludes($include)
            ->toArray();
        return $response;
    }
}