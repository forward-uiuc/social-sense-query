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

			$numberFunctionNames = ['Min','Max', 'Sum'];
			/*
			 * Add min, max, sum for ints
			 */
			foreach($numberFunctionNames as $name) {
				$function = new MetaQueryFunction(['name' => $name. " Ints"]);
				$function->inputs = json_encode(['values*:Int']);
				$function->outputs = json_encode(['value:Int']);
				$function->save();
			}	

			/*
			 * add min max sum for floats
			 */
			foreach($numberFunctionNames as $name) {
				$function = new MetaQueryFunction(['name' => $name. " Floats"]);
				$function->inputs = json_encode(['values*:Float']);
				$function->outputs = json_encode(['value:Float']);
				$function->save();
			}	
			
			
			//
			//Add averages
			$f = new MetaQueryFunction(['name' => 'Average Floats']);
			$f->inputs = json_encode(['values*:Float']);
			$f->outputs = json_encode(['average:Float']);
			$f->save();

			$f = new MetaQueryFunction(['name' => 'Average Ints']);
			$f->inputs = json_encode(['values*:Int']);
			$f->outputs = json_encode(['average:Float']);
			$f->save();


			// Add count
			$f= new MetaQueryFunction(['name' => 'Count']);
			$f->inputs = json_encode(['values*:Any']);
			$f->outputs = json_encode(['count:Int']);
			$f->save();


			// Add limits
			$types = ['Int', 'Float', 'Boolean', 'String'];
			$limits = [5, 10, 20, 50, 100, 500, 1000];
			foreach($types as $type) {
				// Add take k
				foreach($limits as $limit) {
					$f = new MetaQueryFunction(['name' => 'Limit ' . $limit . ' '.$type.'s']);
					$f->inputs = json_encode(['values*:'.$type]);
					$f->outputs = json_encode(['upTo'.$limit.':'.$type.'*']);
					$f->save();
				}
				
				$f = new MetaQueryFunction(['name' => 'Unique '. $type .'s']);
				$f->inputs = json_encode(['values*:'.$type]);
				$f->outputs = json_encode(['uniqueValues:'.$type.'*']);
				$f->save();


				$f = new MetaQueryFunction(['name' => 'Merge '.$type.'s']);
				$f->inputs = json_encode(['values*:'.$type]);
				$f->outputs = json_encode(['mergedValues:'.$type]);
				$f->save();
			}

			$f = new MetaQueryFunction(['name' => 'Remove stop words']);
			$f->inputs = json_encode(['values*:String']);
			$f->outputs = json_encode(['NonStopWords:String*']);
			$f->state = json_encode($this->getStopWords());
			$f->save();

			
			// Add a split by white space function
			$f = new MetaQueryFunction(['name' => 'Split white space']);
			$f->inputs = json_encode(['string:String']);
			$f->outputs = json_encode(['words:String*']);
			$f->save();


			// Add a order by most frequent function for strings
			$f = new MetaQueryFunction(['name' => 'Order by most frequent']);
			$f->inputs = json_encode(['values*:String']);
			$f->outputs = json_encode(['valuesOrderedByMostFrequent:String*']);
			$f->save();

			$f = new MetaQueryFunction(['name' => 'Convert to lower case']);
			$f->inputs = json_encode(['values:String']);
			$f->outputs = json_encode(['lowercaseValues:String']);
			$f->save();

    }

		private function getStopWords() {
			return array(
					'a',
					'about',
					'above',
					'after',
					'again',
					'against',
					'all',
					'am',
					'an',
					'and',
					'any',
					'are',
					"aren't",
					'as',
					'at',
					'be',
					'because',
					'been',
					'before',
					'being',
					'below',
					'between',
					'both',
					'but',
					'by',
					"can't",
					'cannot',
					'could',
					"couldn't",
					'did',
					"didn't",
					'do',
					'does',
					"doesn't",
					'doing',
					"don't",
					'down',
					'during',
					'each',
					'few',
					'for',
					'from',
					'further',
					'had',
					"hadn't",
					'has',
					"hasn't",
					'have',
					"haven't",
					'having',
					'he',
					"he'd",
					"he'll",
					"he's",
					'her',
					'here',
					"here's",
					'hers',
					'herself',
					'him',
					'himself',
					'his',
					'how',
					"how's",
					'i',
					'I',
					"i'd",
					"i'll",
					"i'm",
					"i've",
					'if',
					'in',
					'into',
					'is',
					"isn't",
					'it',
					"it's",
					'its',
					'itself',
					"let's",
					'me',
					'more',
					'most',
					"mustn't",
					'my',
					'myself',
					'no',
					'nor',
					'not',
					'of',
					'off',
					'on',
					'once',
					'only',
					'or',
					'other',
					'ought',
					'our',
					'ours',
					'ourselves',
					'out',
					'over',
					'own',
					'same',
					"shan't",
					'she',
					"she'd",
					"she'll",
					"she's",
					'should',
					"shouldn't",
					'so',
					'some',
					'such',
					'than',
					'that',
					"that's",
					'The',
					'the',
					'their',
					'theirs',
					'them',
					'themselves',
					'then',
					'there',
					"there's",
					'these',
					'they',
					"they'd",
					"they'll",
					"they're",
					"they've",
					'this',
					'those',
					'through',
					'to',
					'too',
					'under',
					'until',
					'up',
					'very',
					'was',
					"wasn't",
					'we',
					"we'd",
					"we'll",
					"we're",
					"we've",
					'were',
					"weren't",
					'what',
					"what's",
					'when',
					"when's",
					'where',
					"where's",
					'which',
					'while',
					'who',
					"who's",
					'whom',
					'why',
					"why's",
					'with',
					"won't",
					'would',
					"wouldn't",
					'you',
					"you'd",
					"you'll",
					"you're",
					"you've",
					'your',
					'yours',
					'yourself',
					'yourselves',
					'zero'
			);
		}
			

}
