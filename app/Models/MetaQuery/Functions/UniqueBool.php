<?php

namespace App\Models\MetaQuery\Functions;

class UniqueBool extends AbstractFunction {
	public static function getName() : string {
		return 'Unique Booleans';
	}
	
	public static function getInputs(): array {
		return ['values:Boolean'];
	}

	public function evaluate($input): array {
		return collect($input->values)->map(function($val){
			return (bool) $val;
		})->unique()->values()->toArray();	
	}

	public static function getOutputs(): array {
		return ['uniqueBools:Boolean'];
	}
}
