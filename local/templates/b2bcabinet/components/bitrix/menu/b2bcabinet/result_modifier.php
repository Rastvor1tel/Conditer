<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(is_object($USER) && $USER->IsAuthorized())
{
	$managerID = '';
	$userID = $USER->GetID();
	$resUser = CUser::GetByID($userID);
	$arUser = $resUser->fetch();
	//TODO -- need add user field: UF_P_MANAGER_ID
	$arResult['PERSONAL_MANAGER_ID'] = $arUser['UF_P_MANAGER_ID'];
}

//if document iblock not selectes
$docIblockID = COption::GetOptionString("sotbit.b2bcabinet","DOCUMENT_IBLOCK_ID","");

//determine if child selected
$bWasSelected = false;
$arParents = array();
$depth = 1;
foreach($arResult as $i=>$arMenu)
{
	if(empty($docIblockID) && strpos($arMenu['LINK'], 'documents'))
		unset($arResult[$i]);

	$depth = $arMenu['DEPTH_LEVEL'];

	if($arMenu['IS_PARENT'] == true)
	{
		$arParents[$arMenu['DEPTH_LEVEL']-1] = $i;
	}
	elseif($arMenu['SELECTED'] == true)
	{
		$bWasSelected = true;
		break;
	}
}

if($bWasSelected)
{
	for($i=0; $i<$depth-1; $i++)
		$arResult[$arParents[$i]]['CHILD_SELECTED'] = true;
}
?>
