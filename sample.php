<head>
<style>
    body {
        font-family: sans-serif;
        font-size: small;
    }
    input {
        font-size: small;
    }
    table {
        layout: fixed-layout;
        width: 500px;
        border-collapse: collapse;
        border: thin solid gray;
        font-size: small;
    }
    th, td {
        border-collapse: collapse;
        border: thin solid gray;
        padding: 2px;
    }
    th {
        background-color: #cccccc;
    }
    .col1 {
        width: 250px;
    }
    .col2 {
        width: 250px;
    }
</style>
</head>

<body>
<table>
    <tr>
        <th class='col1'>Field</th>
        <th class='col2'>Value</th>
    </tr>
    
    <tr>
        <td>Report Name</td>
        <td><input type='text' value='First report' size='30' /></td>
    </tr>
    
    <tr>
        <td>Term</td>
        <td>
            <select>
                <option>1</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>Default grade scale</td>
        <td>
            <select>
                <option>% (100 (highest) to 0 (lowest))</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>Criteria Name</th>
        <th>Grade scale</th>
    </tr>
    <tr>
        <td><input type='text' value='Homework 5%' size='30' /></td>
        <td>
            <div style='display:inline-block;'>
            <select>
                <option>% (100 (highest) to 0 (lowest))</option>
            </select>
            </div>
            <div style='display:inline-block;width:50px;text-align:right'>
            <img src='./images/cross.png' />
            </div>
        </td>
    </tr>  
    <tr>
        <td><input type='text' value='Assessment 5%' size='30' /></td>
        <td>
            <div style='display:inline-block;'>
            <select>
                <option>% (100 (highest) to 0 (lowest))</option>
            </select>
            </div>
            <div style='display:inline-block;width:50px;text-align:right'>
            <img src='./images/cross.png' />
            </div>
        </td>
    </tr>
</table>
    <a href='#'>Add criteria</a>
    <p><button>Copy to all subjects</button></p>
</body>