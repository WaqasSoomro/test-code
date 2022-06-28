<table>
    <tr>
        <td>Requested Player</td>
        <td>{{ $data['name'] }}</td>
    </tr>

    <tr>
        <td>Email</td>
        <td>{{ $data['email'] ?? \Auth::user()->email }}</td>
    </tr>

    <tr>
        <td>Phone</td>
        <td>{{ $data['phone'] }}</td>
    </tr>

    <tr>
        <td>Club</td>
        <td>{{ $data['club'] ?? '' }}</td>
    </tr>

    <tr>
        <td>Team</td>
        <td>{{ $data['team'] ?? '' }}</td>
    </tr>

    <tr>
        <td>Age</td>
        <td>{{ $data['age'] }}</td>
    </tr>
</table>
