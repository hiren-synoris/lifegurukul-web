@extends('admin.mails.mail-layout')
@section('title', $subject)
@section('mail-body')


<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center" valign="middle">
      <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
        <tr>
          <td align="center" valign="top" bgcolor="#f5f5f5" style="border-bottom: #e2e3e3 solid 1px">
            <table width="485" border="0" align="center" cellpadding="0" cellspacing="0" class="two-left-inner">
              <tr>
                <td height="45" align="left" valign="top" style="font-size: 45px; line-height: 45px">
                  &nbsp;
                </td>

              </tr>
              <tr>

                <td align="left" valign="top">
                  <multiline> {{ $content ?? '' }}</multiline>

                 <br>
                 Report has been generated successfully. <a href="{{ url("storage/sales_report/$file") }}" style="color:blue"> Click</a> to download the report.
                 <p style="color:red">Note: Report will be removed after 7 days.</p>
                </td>

              </tr>
              <tr>
                <td height="45" align="left" valign="top" style="font-size: 45px; line-height: 45px">
                  &nbsp;
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

@endsection


