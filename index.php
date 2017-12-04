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

require_once('listFleet.php');

$ATTACKER = $ATTAQUANT;
$DEFENDER = $DEFENSEUR;
for($round = 1;$round <= 6;$round++)
{
	$ROUNDS[$round] = array(
		'Attaquant'=>$ATTACKER,
		'Defenseur'=>$DEFENDER
	);

	#####################################
	#		PHASE DE COMBAT NORMALE		#
	#####################################
	
	// l'attaquant est le premier à agresser !
	foreach($ROUNDS[$round]['Attaquant'] AS $typeAtt=>$amountAtt){
		foreach($ROUNDS[$round]['Defenseur'] AS $typeDef=>$amountDef){
			if($amountDef > 0){
				if(round(((coque($typeDef,$amountDef)/coque($typeDef,$DEFENSEUR[$typeDef])) * 100)) >= 30){ # si la coque à plus de 30 %
					
					$amountDef = Traitement($typeAtt,$amountAtt,$typeDef,$amountDef);
					
				}else{ # dans le cas ou la coque à moins de 30 pourcent 
					if(rand(1,100) <= round(((coque($typeDef,$amountDef)/coque($typeDef,$DEFENSEUR[$typeDef])) * 100))){ # le defenseur à moins de 30 % de chance d'exploser
						$amountDef = 0;
					}					
				}
			}
			
			$ATTACKER[$typeAtt] = intval($amountAtt);
			$DEFENDER[$typeDef] = intval($amountDef);
		}
	}
	$ROUNDS[$round] = array(
		'Attaquant'=>$ATTACKER,
		'Defenseur'=>$DEFENDER
	);
	
	// au tour du défenseur 
	foreach($ROUNDS[$round]['Defenseur'] AS $typeAtt=>$amountAtt){
		foreach($ROUNDS[$round]['Attaquant'] AS $typeDef=>$amountDef){
			if($amountDef > 0){	
				if(round(((coque($typeDef,$amountDef)/coque($typeDef,$ATTAQUANT[$typeDef])) * 100)) >= 30){
					
					$amountDef = Traitement($typeAtt,$amountAtt,$typeDef,$amountDef);
					
				}else{
					if(rand(1,100) <= round(((coque($typeDef,$amountDef)/coque($typeDef,$ATTAQUANT[$typeDef])) * 100))){
						$amountDef = 0;
					}					
				}
			}
			
			$ATTACKER[$typeDef] = intval($amountDef);
			$DEFENDER[$typeAtt] = intval($amountAtt);
		}
	}
	$ROUNDS[$round] = array(
		'Attaquant'=>$ATTACKER,
		'Defenseur'=>$DEFENDER
	);
	
	#####################################
	#    PHASE DE COMBAT RAPIDFIRE  	#
	#####################################
	
	foreach($ROUNDS[$round]['Attaquant'] AS $typeAtt=>$amountAtt){
		foreach($ROUNDS[$round]['Defenseur'] AS $typeDef=>$amountDef){
			if($CombatCaps[$typeAtt]['sd'][$typeDef] > 1){
				if(rand(1,100) <= round((1-(1/$CombatCaps[$typeAtt]['sd'][$typeDef])) * 100)){
					if($amountDef > 0){
						$amountDef = Traitement($typeAtt,$amountAtt,$typeDef,$amountDef);
					}
					
					$ATTACKER[$typeAtt] = intval($amountAtt);
					$DEFENDER[$typeDef] = intval($amountDef);
				}
			}
		}
	}
	
	$ROUNDS[$round] = array(
		'Attaquant'=>$ATTACKER,
		'Defenseur'=>$DEFENDER
	);
	
	foreach($ROUNDS[$round]['Defenseur'] AS $typeAtt=>$amountAtt){
		foreach($ROUNDS[$round]['Attaquant'] AS $typeDef=>$amountDef){
			if($CombatCaps[$typeAtt]['sd'][$typeDef] > 1){
				if(rand(1,100) <= round((1-(1/$CombatCaps[$typeAtt]['sd'][$typeDef])) * 100)){
					if($amountDef > 0){
						$amountDef = Traitement($typeAtt,$amountAtt,$typeDef,$amountDef);
					}
					
					$ATTACKER[$typeDef] = intval($amountDef);
					$DEFENDER[$typeAtt] = intval($amountAtt);
				}
			}
		}
	}
	
	$ROUNDS[$round] = array(
		'Attaquant'=>$ATTACKER,
		'Defenseur'=>$DEFENDER
	);
	
	var_dump($ROUNDS[$round]);
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

function Traitement($typeAtt,$amountAtt,$typeDef,$amountDef)
{
	$ArmeAtt[$typeAtt] = armes($typeAtt,$amountAtt);
	$ShieldDef[$typeDef] = bouclier($typeDef,$amountDef);
	$CoqueDef[$typeDef] = coque($typeDef,$amountDef);
	if($ArmeAtt[$typeAtt] > $ShieldDef[$typeDef]){
		$ArmeAtt[$typeAtt] -= $ShieldDef[$typeDef];
		if($ArmeAtt[$typeAtt] >= $CoqueDef[$typeDef]){
			# la c'est il n'y a plus rien x)
			$CoqueDef [$typeDef]= 0;
			$ShieldDef[$typeDef] = 0;
			$amountDef = 0;
		}else{
			$coqueBefore[$typeDef] = $CoqueDef[$typeDef];
			$CoqueDef[$typeDef] -= $ArmeAtt[$typeAtt];
			$ShieldDef[$typeDef] = 0;
			$amountDef = round((($CoqueDef[$typeDef]/$coqueBefore[$typeDef])*$amountDef));
		}
	}else{
		$ShieldDef[$typeDef] -= $ArmeAtt[$typeAtt];
	}
	
	return $amountDef;
}