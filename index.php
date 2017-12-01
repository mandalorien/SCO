<?php
/**
 * Tis file is part of XNova:Legacies
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see http://www.xnova-ng.org/
 *
 * Copyright (c) 2009-Present, XNova Support Team <http://www.xnova-ng.org>
 * All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *                                --> NOTICE <--
 *  This file is part of the core development branch, changing its contents will
 * make you unable to use the automatic updates manager. Please refer to the
 * documentation for further information about customizing XNova.
 * 
 * use by mandalorien for Project - SCO
 */


require_once('vars.php');

$ROUNDS = array();
$ATTAQUANT = array(
	202 => 500,
	215 => 1000
);
$DEFENSEUR = array(
    202 => 750,
	207 => 1000
);

for($round = 1;$round <= 6;$round++)
{
	$ROUNDS[$round] = array(
		'Attaquant'=>$ATTAQUANT,
		'Defenseur'=>$DEFENSEUR
	);

	// l'attaquant est le premier à agresser !
	foreach($ROUNDS[$round]['Attaquant'] AS $typeAtt=>$amountAtt){
		foreach($ROUNDS[$round]['Defenseur'] AS $typeDef=>$amountDef){
			var_dump($amountDef);
			if($amountDef > 0)
			{
				var_dump('Tour '.$round);
				$ArmeAtt[$typeAtt] = armes($typeAtt,$amountAtt);
				$ShieldDef[$typeDef] = bouclier($typeDef,$amountDef);
				$CoqueDef[$typeDef] = coque($typeDef,$amountDef);
				var_dump('ID vaisseau Att '.$typeAtt .' Attaque ID vaisseau def '.$typeDef);
				if($ArmeAtt[$typeAtt] > $ShieldDef[$typeDef]){
					$ArmeAtt[$typeAtt] -= $ShieldDef[$typeDef];
					var_dump('le bouclier du defenseur n\'a pas tout absorber , il reste '.$ArmeAtt[$typeAtt].' de puissance a l\'attaquant');
					if($ArmeAtt[$typeAtt] >= $CoqueDef[$typeDef]){
						var_dump('la coque du vaisseau '.$typeDef.' du defenseur est détruite');
						# la c'est il n'y a plus rien x)
						$CoqueDef [$typeDef]= 0;
						$ShieldDef[$typeDef] = 0;
						$amountDef = 0;
					}else{
						var_dump(''.$ArmeAtt[$typeAtt].' de puissance a l\'attaquant');
						var_dump('la coque du vaisseau '. $CoqueDef[$typeDef] .' du defenseur');
						$coqueBefore[$typeDef] = $CoqueDef[$typeDef];
						$CoqueDef[$typeDef] -= $ArmeAtt[$typeAtt];
						var_dump('la coque du vaisseau '.$typeDef.' du defenseur a absorbe ' .$CoqueDef[$typeDef]);
						$ShieldDef[$typeDef] = 0;
						$amountDef = round((($CoqueDef[$typeDef]/$coqueBefore[$typeDef])*$amountDef));
					}
				}else{
					$ShieldDef[$typeDef] -= $ArmeAtt[$typeAtt];
					var_dump('le bouclier du defenseur a tous absorber mais il lui reste '.$ShieldDef[$typeDef].' de bouclier');
				}
			}
			
			$ATTAQUANT[$typeAtt] = intval($amountAtt);
			$DEFENSEUR[$typeDef] = intval($amountDef);
		}
	}
	$ROUNDS[$round] = array(
		'Attaquant'=>$ATTAQUANT,
		'Defenseur'=>$DEFENSEUR
	);
	
	var_dump('On enregistre le nouveau tableau !');
	var_dump($ROUNDS[$round]);
	
	// le defenseur replique
	foreach($ROUNDS[$round]['Defenseur'] AS $typeDef=>$amountDef){
		foreach($ROUNDS[$round]['Attaquant'] AS $typeAtt=>$amountAtt){
			// if($amountAtt > 0)
			// {
				// $ArmeDef = armes($typeDef,$amountDef);
				// $ShieldAtt = bouclier($typeAtt,$amountAtt);
				// $CoqueAtt = coque($typeAtt,$amountAtt);
				// if($ArmeDef > $ShieldAtt){
					// $NewArmeDef = $ArmeDef - $ShieldAtt;
					// $ArmeDef = $NewArmeDef;
					// if($ArmeDef >= $CoqueAtt){
						// # la c'est il n'y a plus rien x)
						// $NewCoqueAtt = 0;
						// $NewShieldAtt = 0;
						// $NewAmountAtt = 0;
					// }else{
						// $NewCoqueAtt = $CoqueAtt - $ArmeDef;
						// $NewShieldAtt = 0;
						// $NewAmountAtt =$amountAtt - round(($NewCoqueAtt/$amountAtt));
					// }
				// }else{
					// $NewCoqueAtt = $CoqueAtt;
					// $NewShieldAtt = $ShieldAtt - $ArmeDef;
					// $NewAmountAtt = $amountAtt;
				// }
			// }
			// $defend[] = array($typeDef=>$NewAmountDef);
			// $Attack[] = array($typeAtt=>$amountDef);
		}
	}

	// $ROUNDS[$round]['Attaquant'] = $Attack;
	// $ROUNDS[$round]['Defenseur'] = $defend;
}

function coque($type,$amount){
	global $pricelist;
	return (($pricelist[$type]['metal'] + $pricelist[$type]['crystal'])/10) * $amount;
}

function bouclier($type,$amount){
	global $CombatCaps;
	return $CombatCaps[$type]['shield'] * $amount;
}

function armes($type,$amount){
	global $CombatCaps;
	return $CombatCaps[$type]['attack'] * $amount;
}