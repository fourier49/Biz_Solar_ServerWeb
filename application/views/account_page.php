<script type="text/javascript" src="<?=base_url('js/ext-lib/models/grid/account.js')?>"></script>

<div id="title">Account</div>

<div class="toolbar" style="text-align: left; font-weight:bold;">
<a href="<?=site_url("home/sub_account/".$client['client_id']);?>">Main Account - <?=$client['account']?></a>
<font style="margin-left:5px; font-weight:normal;">--- Click to check detail
</div>

<br>
<div id="account-grid"></div>
