<?php

use Illuminate\Database\Seeder;

use App\Models\MetaQuery\MetaQueryFunction;

class MetaQueryFunctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
			$numberFunctionNames = ['Min','Max','Average', 'Sum'];

			foreach($numberFunctionNames as $name) {
				$function = new MetaQueryFunction(['name' => $name. " Ints"]);
				$function->inputs = json_encode(['values:Int']);
				$function->outputs = json_encode(['value:Int']);
				$function->save();
			}	

			foreach($numberFunctionNames as $name) {
				$function = new MetaQueryFunction(['name' => $name. " Floats"]);
				$function->inputs = json_encode(['values:Float']);
				$function->outputs = json_encode(['value:Float']);
				$function->save();
			}	
			
			$function = new MetaQueryFunction(['name' => 'Count']);
			$function->inputs = json_encode(['values:Any']);
			$function->outputs = json_encode(['count:Int']);
			$function->save();
    }
}
