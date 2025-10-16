<form action="/payment/initiate" method="POST">
    @csrf
    <label for="amount">Amount</label>
    <input type="number" name="amount" id="amount" required>
    <button type="submit">Pay Now</button>
</form>
