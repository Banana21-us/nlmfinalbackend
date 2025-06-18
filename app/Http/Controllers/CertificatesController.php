<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Models\certificates;
use App\Http\Requests\StorecertificatesRequest;
use App\Http\Requests\UpdatecertificatesRequest;

class CertificatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function postcert(Request $request)
{
    $request->validate([
        'userid' => 'required|exists:users,id',
        'name' => 'required|string|max:255',
        'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif|max:5120', // Updated mimes
    ]);

    try {
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        // Generate filename: originalname_timestamp.ext
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $timestamp = time();
        $filename = "{$originalName}_{$timestamp}.{$extension}";
        
        // Store the file
        $path = $file->storeAs('certificates', $filename, 'public');
        
        // Create certificate record
        $certificate = Certificates::create([
            'userid' => $request->userid,
            'name' => $request->name,
            'file' => $filename,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => $certificate
        ], 201);

    } catch (\Exception $e) {
        Log::error('File upload failed: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to upload file',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function showcertbyid($userid)
{
    // Get all certificates for the specified user
    $certificates = Certificates::where('userid', $userid)->get();

    if ($certificates->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No certificates found for this user'
        ], 404);
    }

    // Format the response with file URLs
    $formattedCertificates = $certificates->map(function ($certificate) {
        return [
            'id' => $certificate->id,
            'name' => $certificate->name,
            'file' => $certificate->file,
            'file_url' => asset('storage/certificates/' . $certificate->file),
            'created_at' => $certificate->created_at->format('Y-m-d H:i:s')
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $formattedCertificates
    ]);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatecertificatesRequest $request, certificates $certificates)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deletecert($id)
    {
        try {
            // Find the certificate by ID
            $certificate = Certificates::findOrFail($id);

            // Get the file path
            $filePath = storage_path('app/public/certificates/' . $certificate->file);

            // Delete the file from storage
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete the database record
            $certificate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Certificate deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
