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

class Combat{
	
	const TOURS = 6;
	private $_round;
	private $_ROUNDS = array();
	private $_ATTACKER = array();
	private $_ATTAQUANT = array();
	private $_DEFENDER = array();
	private $_DEFENSEUR = array();
	private $_Pricelist = array();
	private $_Combatcap = array();
	
	
	public function __construct($A,$B){
		global $pricelist,$CombatCaps;
		$this->_round = 1;
		$this->_ATTACKER = $A;
		$this->_DEFENDER = $B;
		$this->_ATTAQUANT = $A;
		$this->_DEFENSEUR = $B;
		$this->_Pricelist = $pricelist;
		$this->_Combatcap = $CombatCaps;
	}
	
	private function coque($type,$nombre){
		return (($this->_Pricelist[$type]['metal'] + $this->_Pricelist[$type]['crystal'])/10) * $nombre;
	}

	private function bouclier($type,$nombre){
		return $this->_Combatcap[$type]['shield'] * $nombre;
	}

	private function arme($type,$nombre){
		return $this->_Combatcap[$type]['attack'] * $nombre;
	}
	
	private function Traitement($typeAtt,$amountAtt,$typeDef,$amountDef)
	{
		$ArmeAtt[$typeAtt]		= $this->arme($typeAtt,$amountAtt);
		$ShieldDef[$typeDef]	= $this->bouclier($typeDef,$amountDef);
		$CoqueDef[$typeDef]		= $this->coque($typeDef,$amountDef);
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

	private function PhaseAttaquant($round){
		foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeAtt=>$amountAtt){
			foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeDef=>$amountDef){
				if($amountDef > 0){
					if(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_DEFENSEUR[$typeDef])) * 100)) >= 30){ # si la coque à plus de 30 %
						
						$amountDef = $this->Traitement($typeAtt,$amountAtt,$typeDef,$amountDef);
						
					}else{ # dans le cas ou la coque à moins de 30 pourcent 
						if(rand(1,100) <= round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_DEFENSEUR[$typeDef])) * 100))){ # le defenseur à moins de 30 % de chance d'exploser
							$amountDef = 0;
						}					
					}
				}
				
				$this->_ATTACKER[$typeAtt] = intval($amountAtt);
				$this->_DEFENDER[$typeDef] = intval($amountDef);
			}
		}
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
	}
	
	private function PhaseDefenseur($round){
		foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeAtt=>$amountAtt){
			foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeDef=>$amountDef){
				if($amountDef > 0){	
					if(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_ATTAQUANT[$typeDef])) * 100)) >= 30){
						
						$amountDef = $this->Traitement($typeAtt,$amountAtt,$typeDef,$amountDef);
						
					}else{
						if(rand(1,100) <= round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_ATTAQUANT[$typeDef])) * 100))){
							$amountDef = 0;
						}					
					}
				}
				
				$this->_ATTACKER[$typeDef] = intval($amountDef);
				$this->_DEFENDER[$typeAtt] = intval($amountAtt);
			}
		}
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
	}
	
	private function PhaseRapidFire($round){
		
		foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeAtt=>$amountAtt){
			foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeDef=>$amountDef){
				if($this->_Combatcap[$typeAtt]['sd'][$typeDef] > 1){
					if(rand(1,100) <= round((1-(1/$this->_Combatcap[$typeAtt]['sd'][$typeDef])) * 100)){
						if($amountDef > 0){
							if(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_DEFENSEUR[$typeDef])) * 100)) >= 30){ # si la coque à plus de 30 %
								
								$amountDef = $this->Traitement($typeAtt,$amountAtt,$typeDef,$amountDef);
								
							}else{ # dans le cas ou la coque à moins de 30 pourcent 
								if(rand(1,100) <= round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_DEFENSEUR[$typeDef])) * 100))){ # le defenseur à moins de 30 % de chance d'exploser
									$amountDef = 0;
								}					
							}
						}
						
						$this->_ATTACKER[$typeAtt] = intval($amountAtt);
						$this->_DEFENDER[$typeDef] = intval($amountDef);
					}
				}
			}
		}
		
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
		
		foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeAtt=>$amountAtt){
			foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeDef=>$amountDef){
				if($this->_Combatcap[$typeAtt]['sd'][$typeDef] > 1){
					if(rand(1,100) <= round((1-(1/$this->_Combatcap[$typeAtt]['sd'][$typeDef])) * 100)){
						if($amountDef > 0){	
							if(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_ATTAQUANT[$typeDef])) * 100)) >= 30){
								
								$amountDef = $this->Traitement($typeAtt,$amountAtt,$typeDef,$amountDef);
								
							}else{
								if(rand(1,100) <= round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_ATTAQUANT[$typeDef])) * 100))){
									$amountDef = 0;
								}					
							}
						}
						
						$this->_ATTACKER[$typeDef] = intval($amountDef);
						$this->_DEFENDER[$typeAtt] = intval($amountAtt);
					}
				}
			}
		}
		
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
	}
	
	public function PhaseResultat(){
		for($this->_round = 1;$this->_round <= self::TOURS;$this->_round ++)
		{
			$this->ROUNDS[$this->_round] = array(
				'Attaquant'=>$this->_ATTACKER,
				'Defenseur'=>$this->_DEFENDER
			);
			
			$this->PhaseAttaquant($this->_round);
			$this->PhaseDefenseur($this->_round);
			$this->PhaseRapidFire($this->_round);
		}
		return $this->ROUNDS;
	}
}
?>