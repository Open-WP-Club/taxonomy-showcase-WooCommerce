import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUploadCheck,
	MediaUpload,
	PanelColorSettings,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	ToggleControl,
	TextControl,
	Button,
	Spinner,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const [ taxonomyOptions, setTaxonomyOptions ] = useState( null );

	useEffect( () => {
		apiFetch( { path: '/woo-taxonomy-blocks/v1/taxonomies' } )
			.then( ( data ) => setTaxonomyOptions( data ) )
			.catch( () =>
				setTaxonomyOptions( [
					{ value: 'product_cat', label: __( 'Product Categories', 'woo-taxonomy-blocks' ) },
				] )
			);
	}, [] );

	const {
		taxonomy,
		limit,
		orderby,
		order,
		hideEmpty,
		columns,
		aspectRatio,
		imageSize,
		showCount,
		showDescription,
		cardBorderRadius,
		cardShadow,
		hoverEffect,
		placeholderImageId,
		placeholderColor,
		showAlphabetFilter,
		showSearch,
		searchPlaceholder,
	} = attributes;

	const placeholderImageUrl = useSelect(
		( select ) => {
			if ( ! placeholderImageId ) return null;
			return select( 'core' ).getMedia( placeholderImageId )?.source_url ?? null;
		},
		[ placeholderImageId ]
	);

	return (
		<div { ...blockProps }>
			<InspectorControls>
				{ /* ── Content ── */ }
				<PanelBody title={ __( 'Content', 'woo-taxonomy-blocks' ) } initialOpen={ true }>
					{ taxonomyOptions === null ? (
						<Spinner />
					) : (
						<SelectControl
							label={ __( 'Taxonomy', 'woo-taxonomy-blocks' ) }
							value={ taxonomy }
							options={ taxonomyOptions }
							onChange={ ( val ) => setAttributes( { taxonomy: val } ) }
						/>
					) }
					<RangeControl
						label={ __( 'Number of terms', 'woo-taxonomy-blocks' ) }
						value={ limit }
						min={ 1 }
						max={ 24 }
						onChange={ ( val ) => setAttributes( { limit: val } ) }
					/>
					<SelectControl
						label={ __( 'Order by', 'woo-taxonomy-blocks' ) }
						value={ orderby }
						options={ [
							{ value: 'name',  label: __( 'Name', 'woo-taxonomy-blocks' ) },
							{ value: 'count', label: __( 'Product count', 'woo-taxonomy-blocks' ) },
							{ value: 'id',    label: __( 'ID', 'woo-taxonomy-blocks' ) },
							{ value: 'slug',  label: __( 'Slug', 'woo-taxonomy-blocks' ) },
						] }
						onChange={ ( val ) => setAttributes( { orderby: val } ) }
					/>
					<SelectControl
						label={ __( 'Order', 'woo-taxonomy-blocks' ) }
						value={ order }
						options={ [
							{ value: 'ASC',  label: __( 'Ascending', 'woo-taxonomy-blocks' ) },
							{ value: 'DESC', label: __( 'Descending', 'woo-taxonomy-blocks' ) },
						] }
						onChange={ ( val ) => setAttributes( { order: val } ) }
					/>
					<ToggleControl
						label={ __( 'Hide empty terms', 'woo-taxonomy-blocks' ) }
						checked={ hideEmpty }
						onChange={ ( val ) => setAttributes( { hideEmpty: val } ) }
					/>
				</PanelBody>

				{ /* ── Layout ── */ }
				<PanelBody title={ __( 'Layout', 'woo-taxonomy-blocks' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Live search', 'woo-taxonomy-blocks' ) }
						help={ __( 'Show a search input that filters terms as you type.', 'woo-taxonomy-blocks' ) }
						checked={ showSearch }
						onChange={ ( val ) => setAttributes( { showSearch: val } ) }
					/>
					{ showSearch && (
						<TextControl
							label={ __( 'Search placeholder text', 'woo-taxonomy-blocks' ) }
							value={ searchPlaceholder }
							placeholder={ __( 'Search…', 'woo-taxonomy-blocks' ) }
							onChange={ ( val ) => setAttributes( { searchPlaceholder: val } ) }
						/>
					) }
					<ToggleControl
						label={ __( 'Alphabet index', 'woo-taxonomy-blocks' ) }
						help={ __( 'Group terms A–Z with a sticky navigation. Works best with Order by: Name.', 'woo-taxonomy-blocks' ) }
						checked={ showAlphabetFilter }
						onChange={ ( val ) => setAttributes( { showAlphabetFilter: val } ) }
					/>
					<RangeControl
						label={ __( 'Columns', 'woo-taxonomy-blocks' ) }
						value={ columns }
						min={ 1 }
						max={ 6 }
						onChange={ ( val ) => setAttributes( { columns: val } ) }
					/>
					<SelectControl
						label={ __( 'Aspect ratio', 'woo-taxonomy-blocks' ) }
						value={ aspectRatio }
						options={ [
							{ value: '1/1',  label: '1:1' },
							{ value: '4/3',  label: '4:3' },
							{ value: '3/2',  label: '3:2' },
							{ value: '16/9', label: '16:9' },
						] }
						onChange={ ( val ) => setAttributes( { aspectRatio: val } ) }
					/>
					<SelectControl
						label={ __( 'Image size', 'woo-taxonomy-blocks' ) }
						value={ imageSize }
						options={ [
							{ value: 'thumbnail', label: __( 'Thumbnail', 'woo-taxonomy-blocks' ) },
							{ value: 'medium',    label: __( 'Medium', 'woo-taxonomy-blocks' ) },
							{ value: 'large',     label: __( 'Large', 'woo-taxonomy-blocks' ) },
							{ value: 'full',      label: __( 'Full', 'woo-taxonomy-blocks' ) },
						] }
						onChange={ ( val ) => setAttributes( { imageSize: val } ) }
					/>
				</PanelBody>

				{ /* ── Card ── */ }
				<PanelBody title={ __( 'Card', 'woo-taxonomy-blocks' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show product count', 'woo-taxonomy-blocks' ) }
						checked={ showCount }
						onChange={ ( val ) => setAttributes( { showCount: val } ) }
					/>
					<ToggleControl
						label={ __( 'Show description', 'woo-taxonomy-blocks' ) }
						checked={ showDescription }
						onChange={ ( val ) => setAttributes( { showDescription: val } ) }
					/>
				</PanelBody>

				{ /* ── Style ── */ }
				<PanelBody title={ __( 'Style', 'woo-taxonomy-blocks' ) } initialOpen={ false }>
					<RangeControl
						label={ __( 'Border radius (px)', 'woo-taxonomy-blocks' ) }
						value={ cardBorderRadius }
						min={ 0 }
						max={ 32 }
						onChange={ ( val ) => setAttributes( { cardBorderRadius: val } ) }
					/>
					<ToggleControl
						label={ __( 'Card shadow', 'woo-taxonomy-blocks' ) }
						checked={ cardShadow }
						onChange={ ( val ) => setAttributes( { cardShadow: val } ) }
					/>
					<SelectControl
						label={ __( 'Hover effect', 'woo-taxonomy-blocks' ) }
						value={ hoverEffect }
						options={ [
							{ value: 'none', label: __( 'None', 'woo-taxonomy-blocks' ) },
							{ value: 'lift', label: __( 'Lift', 'woo-taxonomy-blocks' ) },
							{ value: 'zoom', label: __( 'Zoom image', 'woo-taxonomy-blocks' ) },
							{ value: 'glow', label: __( 'Glow', 'woo-taxonomy-blocks' ) },
						] }
						onChange={ ( val ) => setAttributes( { hoverEffect: val } ) }
					/>
				</PanelBody>

				{ /* ── Placeholder ── */ }
				<PanelBody title={ __( 'Placeholder', 'woo-taxonomy-blocks' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => setAttributes( { placeholderImageId: media.id } ) }
							allowedTypes={ [ 'image' ] }
							value={ placeholderImageId }
							render={ ( { open } ) => (
								<>
									{ placeholderImageUrl && (
										<img
											src={ placeholderImageUrl }
											alt=""
											style={ {
												width: '100%',
												marginBottom: '8px',
												borderRadius: '4px',
												display: 'block',
											} }
										/>
									) }
									<Button
										variant="secondary"
										onClick={ open }
										style={ { width: '100%', justifyContent: 'center', marginBottom: '4px' } }
									>
										{ placeholderImageId
											? __( 'Change placeholder image', 'woo-taxonomy-blocks' )
											: __( 'Upload placeholder image', 'woo-taxonomy-blocks' ) }
									</Button>
									{ placeholderImageId && (
										<Button
											variant="link"
											isDestructive
											onClick={ () => setAttributes( { placeholderImageId: 0 } ) }
										>
											{ __( 'Remove', 'woo-taxonomy-blocks' ) }
										</Button>
									) }
								</>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>

				<PanelColorSettings
					title={ __( 'Placeholder color', 'woo-taxonomy-blocks' ) }
					initialOpen={ false }
					colorSettings={ [
						{
							value:    placeholderColor,
							onChange: ( val ) => setAttributes( { placeholderColor: val ?? '#f0f0f0' } ),
							label:    __( 'Background color', 'woo-taxonomy-blocks' ),
						},
					] }
				/>
			</InspectorControls>

			<ServerSideRender
				block="woo-taxonomy-blocks/taxonomy-grid"
				attributes={ attributes }
				EmptyResponsePlaceholder={ () => (
					<p style={ { padding: '2rem', textAlign: 'center', color: '#666', border: '1px dashed #ccc' } }>
						{ __( 'No terms found. Select a taxonomy or adjust your settings.', 'woo-taxonomy-blocks' ) }
					</p>
				) }
			/>
		</div>
	);
}
