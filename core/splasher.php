<?php 
	class Splasher {

		public static function GetRandomSplash(): string {
			$splashes = file($_SERVER["DOCUMENT_ROOT"]."/core/splashes.txt");
			return $splashes[array_rand($splashes)];
		}

		public static function GenerateSplashHeader(): void {
			$splash = self::GetRandomSplash();

			if(str_starts_with($splash, "!array")) {
				$splash = substr($splash, strlen("!array"));
				echo <<< EOT
					<div style="color: white;padding: 5px;border: 1px solid black;border-top: 0;background: darkmagenta;">
						<span style="font-size: 12px;">$splash</span>
					</div>
				EOT;
			} else {
				echo <<< EOT
					<div style="color: white;padding: 5px;border: 1px solid black;border-top: 0;background: orange;">
						<span style="font-size: 12px;">$splash</span>
					</div>
				EOT;
			}
		}

	}