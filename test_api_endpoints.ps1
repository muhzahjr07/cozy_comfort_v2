# test_api_endpoints.ps1
$baseUrl = "http://localhost:5262"

function Test-Endpoint {
    param (
        [string]$Name,
        [string]$Method,
        [string]$Route,
        [object]$Body,
        [string]$ExpectedStatus = "200"
    )

    Write-Host "--------------------------------------------------"
    Write-Host "Running Test: $Name" -ForegroundColor Cyan
    Write-Host "$Method $baseUrl$Route" -ForegroundColor Gray

    try {
        $params = @{
            Uri = "$baseUrl$Route"
            Method = $Method
            ErrorAction = "Stop"
        }

        if ($Body) {
            # Force array serialization for lists
            $json = $Body | ConvertTo-Json -Depth 10 -Compress
            
            # PowerShell 5.1 Hack: If input was an array but output is a single object, wrap it
            if ($Body -is [Array] -and $json.StartsWith("{")) {
                $json = "[$json]"
            }

            $params.Add("Body", $json)
            $params.Add("ContentType", "application/json")
        }

        $response = Invoke-RestMethod @params
        Write-Host "✅ Success! Response:" -ForegroundColor Green
        
        # Try to pretty print response
        try {
            $response | ConvertTo-Json -Depth 5 | Write-Host
        } catch {
            $response | Write-Host
        }
    }
    catch {
        $ex = $_.Exception
        if ($ex.Response) {
            $status = [int]$ex.Response.StatusCode
            if ("$status" -eq "$ExpectedStatus" -or "$ExpectedStatus" -eq "400") {
                Write-Host "✅ Pass (Expected Error): $($ex.Message)" -ForegroundColor Green
                try {
                    $reader = New-Object System.IO.StreamReader($ex.Response.GetResponseStream())
                    Write-Host "Error Details: $($reader.ReadToEnd())" -ForegroundColor Yellow
                } catch {}
            }
            else {
                Write-Host "❌ Failed with Status $status" -ForegroundColor Red
                Write-Host $ex.Message
                try {
                    $reader = New-Object System.IO.StreamReader($ex.Response.GetResponseStream())
                    Write-Host "Error Details: $($reader.ReadToEnd())" -ForegroundColor Yellow
                } catch {}
            }
        }
        else {
            Write-Host "❌ Failed: $($ex.Message)" -ForegroundColor Red
            Write-Host "Ensure the backend is running on $baseUrl" -ForegroundColor Yellow
        }
    }
    Write-Host ""
}

# Test 1: Check Stock (Sufficient Seller Stock)
Test-Endpoint -Name "Test 1: Check Stock (Sufficient Seller Stock)" -Method "POST" -Route "/api/Fulfillment/check-stock" -Body @(@{ productId="blanket-001"; quantity=2 })

# Test 2: Check Stock (Distributor Escalation)
Test-Endpoint -Name "Test 2: Check Stock (Distributor Escalation)" -Method "POST" -Route "/api/Fulfillment/check-stock" -Body @(@{ productId="blanket-001"; quantity=7 })

# Test 3: Check Stock (Manufacturer Escalation)
Test-Endpoint -Name "Test 3: Check Stock (Manufacturer Escalation)" -Method "POST" -Route "/api/Fulfillment/check-stock" -Body @(@{ productId="blanket-002"; quantity=10 })

# Test 4: Place Order
Test-Endpoint -Name "Test 4: Place Order (blanket-001, quantity 2)" -Method "POST" -Route "/api/Fulfillment/place-order" -Body @(@{ productId="blanket-001"; quantity=2 })

# Test 5: Get Inventory State
Test-Endpoint -Name "Test 5: Get Live Inventory State" -Method "GET" -Route "/api/Fulfillment/inventory"
