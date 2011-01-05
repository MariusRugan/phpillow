<?php
/**
 * Basic test cases for model connections
 *
 * @version $Revision$
 * @license GPLv3
 */

/**
 * Tests for the basic model
 */
class phpillowDocumentTests extends PHPUnit_Framework_TestCase
{
    /**
     * Return test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
	public static function suite()
	{
		return new PHPUnit_Framework_TestSuite( __CLASS__ );
	}

    public function setUp()
    {
        phpillowTestEnvironmentSetup::resetDatabase(
            array( 
                'database' => 'test',
            )
        );
        phpillowManager::setDocumentClass( 'user', 'phpillowUserDocument' );
    }

    public function testIdFromString()
    {
        // These tests do only work with a minimum glibc version of 2.7 and
        // depend on the installed locale. You may see different results, and
        // we include a set of possible valid results in the tests.
        if ( version_compare( ICONV_VERSION, '2.7', '<' ) )
        {
            $this->markTestSkipped( 'Minimum glibs version 2.7 required.' );
        }

        $document = new phpillowDocumentAllPublic();

        $this->assertSame(
            'kore',
            $document->stringToId( 'kore' )
        );

        $this->assertTrue(
            in_array( 
                $string = $document->stringToId( 'öäü' ),
                array( 
                    '_',
                    'oau', 
                    'oeaeue'
                )
            ),
            "1 String '$string' not in valid expectations."
        );

        $this->assertTrue(
            in_array( 
                $string = $document->stringToId( 'Žluťoučký kůň' ),
                array( 
                    '_lu_ou_k_k_', 
                    'zlutoucky_kun', 
                )
            ),
            "2 String '$string' not in valid expectations."
        );

        $this->assertTrue(
            in_array( 
                $string = $document->stringToId( '!"§$%&/(=)Ä\'Ö*``\'"' ),
                array( 
                    '_',
                    '_a_o_',
                    '_ae_oe_'
                )
            ),
            "3 String '$string' not in valid expectations."
        );

        $this->assertTrue(
            in_array( 
                $string = $document->stringToId( '!"§$%&/(=)Ä\'Ö*``\'"', '-' ),
                array( 
                    '-',
                    '-a-o-', 
                    '-ae-oe-'
                )
            ),
            "4 String '$string' not in valid expectations."
        );
    }

    public function testCreateAndStoreUser()
    {
        $doc = phpillowUserDocument::createNew();

        try
        {
            $doc->save();
            $this->fail( 'Expected phpillowRuntimeException.' );
        }
        catch ( phpillowRuntimeException $e )
        { /* Expected exception */ }

        $doc->login = 'kore';
        $doc->save();
    }

    public function testCreateDocumentWithAutogeneratedId()
    {
        $doc = phpillowTestNullIdDocument::createNew();
        $doc->login = 'kore';
        $doc->save();

        $this->assertTrue(
            (bool) preg_match( '(^[a-f0-9]+$)', $doc->_id )
        );
    }

    public function testCreateNewDocumentWithoutProperties()
    {
        $doc = phpillowTestNullIdDocument::createNew();
        $doc->save();

        $this->assertTrue(
            (bool) preg_match( '(^[a-f0-9]+$)', $doc->_id )
        );
    }

    public function testFetchDocumentByEmptyId()
    {
        try
        {
            $doc = phpillowManager::fetchDocument( 'user', '' );
            $this->fail( 'Expected phpillowResponseNotFoundErrorException.' );
        }
        catch ( phpillowResponseNotFoundErrorException $e )
        {
            $this->assertEquals(
                'Error (404) in request: not_found (No document ID specified.).',
                $e->getMessage()
            );
        }
    }

    public function testFetchDocumentById()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->save();

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );

        $this->assertSame(
            'kore',
            $doc->login
        );

        $this->assertSame(
            null,
            $doc->name
        );
    }

    public function testDocumentFetchAndChange()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->save();

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $doc->name = 'Kore (update)';
        $doc->save();

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $doc->name = 'Kore (update)';

        $this->assertSame(
            'Kore (update)',
            $doc->name
        );
    }

    public function testDocumentFetchAndStoreUnmodified()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->save();

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $this->assertFalse(
            $doc->save()
        );
    }

    public function testDocumentGetUnknownProperty()
    {
        $doc = phpillowUserDocument::createNew();

        try
        {
            $doc->unknown;
            $this->fail( 'Expected phpillowNoSuchPropertyException.' );
        }
        catch ( phpillowNoSuchPropertyException $e )
        { /* Expected exception */ }
    }

    public function testDocumentIssetKnownProperty()
    {
        $doc = phpillowUserDocument::createNew();

        $this->assertTrue( isset( $doc->name ) );
        $this->assertTrue( isset( $doc->_id ) );
    }

    public function testDocumentIssetUnknownProperty()
    {
        $doc = phpillowUserDocument::createNew();

        $this->assertFalse( isset( $doc->unknown ) );
    }

    public function testDocumentSetUnknownProperty()
    {
        $doc = phpillowUserDocument::createNew();

        try
        {
            $doc->unknown = 'foo';
            $this->fail( 'Expected phpillowNoSuchPropertyException.' );
        }
        catch ( phpillowNoSuchPropertyException $e )
        { /* Expected exception */ }
    }

    public function testDocumentFetchAndChangeRevisions()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $doc->save();

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $doc->login = 'Kore_2';
        $doc->name = 'Kore Nordmann';
        $doc->save();

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );
        $doc->name = 'Kore D. Nordmann';
        $doc->save();

        $doc = phpillowManager::fetchDocument( 'user', 'user-kore' );

        $this->assertSame(
            'kore',
            $doc->revisions[0]['login']
        );

        $this->assertSame(
            'Kore Nordmann',
            $doc->revisions[1]['name']
        );

        $this->assertSame(
            'Kore D. Nordmann',
            $doc->revisions[2]['name']
        );
    }

    public function testDeleteDocument()
    {
        $doc = phpillowUserDocument::createNew();
        $doc->login = 'kore';
        $id = $doc->save();

        $doc = phpillowManager::fetchDocument( 'user', $id );
        $response = $doc->delete();

        $this->assertTrue( $response->ok );

        try
        {
            phpillowManager::fetchDocument( 'user', $id );
            $this->fail( 'Expected not found exception.' );
        }
        catch ( phpillowResponseNotFoundErrorException $e )
        { /* Expected */ }
    }
}

